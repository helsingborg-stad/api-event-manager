<?php

/**
 * Plugin Name:       Event Manager
 * Plugin URI:        https://github.com/helsingborg-stad/api-event-manager
 * Description:       Creates a api that may be used to manage events
 * Version: 2.18.0
 * Author:            Thor Brink, Sebastian Thulin @ Helsingborg Stad
 * Author URI:        https://github.com/helsingborg-stad
 * License:           MIT
 * License URI:       https://opensource.org/licenses/MIT
 * Text Domain:       api-event-manager
 * Domain Path:       /languages
 */

use EventManager\CleanupUnusedTags\CleanupUnusedTags;
use EventManager\HooksRegistrar\HooksRegistrar;
use EventManager\PostTableColumns\Column as PostTableColumn;
use EventManager\PostTableColumns\ColumnCellContent\NestedMetaStringCellContent;
use EventManager\PostTableColumns\ColumnCellContent\TermNameCellContent;
use EventManager\PostTableColumns\ColumnSorters\MetaStringSort;
use EventManager\PostTableColumns\ColumnSorters\NestedMetaStringSort;
use EventManager\PostTableColumns\Helpers\GetNestedArrayStringValueRecursive;
use EventManager\PostToSchema\Mappers\StringToEventSchemaMapper;
use EventManager\PostToSchema\PostToEventSchema\PostToEventSchema;
use EventManager\Services\AcfService\AcfServiceFactory;
use EventManager\SetPostTermsFromContent\SetPostTermsFromContent;
use EventManager\TagReader\TagReader;
use EventManager\ApiResponseModifiers\EventResponseModifier;
use EventManager\ContentExpirationManagement\ExpiredEvents;
use EventManager\CronScheduler\CronScheduler;
use EventManager\PostToSchema\PostToEventSchema\Commands\Helpers\CommandHelpers;
use EventManager\Resolvers\FileSystem\ManifestFilePathResolver;
use EventManager\Resolvers\FileSystem\UrlFilePathResolver;
use EventManager\Services\FileSystem\FileSystemFactory;
use EventManager\Services\WPService\Implementations\FilePathResolvingWpService;
use EventManager\Services\WPService\Implementations\NativeWpService;
use EventManager\Services\WPService\Implementations\WpServiceLazyDecorator;

// Protect against direct file access
if (!defined('WPINC')) {
    die;
}

define('EVENT_MANAGER_PATH', plugin_dir_path(__FILE__));
define('EVENT_MANAGER_URL', plugins_url('', __FILE__));
define('EVENT_MANAGER_TEMPLATE_PATH', EVENT_MANAGER_PATH . 'templates/');

/**
 * Composer autoload
 */
if (file_exists(EVENT_MANAGER_PATH . 'vendor/autoload.php')) {
    require EVENT_MANAGER_PATH . '/vendor/autoload.php';
}

$hooksRegistrar = new HooksRegistrar();
$acfService     = AcfServiceFactory::create();

$manifestFileWpService = new WpServiceLazyDecorator();
$wpService             = new FilePathResolvingWpService(
    new NativeWpService(),
    new ManifestFilePathResolver(
        EVENT_MANAGER_PATH . "dist/manifest.json",
        FileSystemFactory::create(),
        $manifestFileWpService,
        new UrlFilePathResolver($manifestFileWpService)
    )
);

$manifestFileWpService->setInner($wpService);

/**
 * Load text domain
 */
$loadTextDomain = new \EventManager\Helper\LoadTextDomain($wpService);

$hooksRegistrar->register($loadTextDomain);

/**
 * Acf export manager
 */
$acfExportManager = new \EventManager\Helper\RegisterAcfExportManager($wpService);

$hooksRegistrar->register($acfExportManager);

/**
 * Settings
 */
$adminSettingsPage = new \EventManager\Settings\AdminSettingsPage($wpService, $acfService);

$hooksRegistrar->register($adminSettingsPage);

/**
 * Convert REST api posts to event schemas.
 */
$stringToEventSchemaMapper         = new StringToEventSchemaMapper();
$postToSchemaAdapterCommandHelpers = new CommandHelpers();
$postToSchemaAdapter               = new PostToEventSchema($stringToEventSchemaMapper, $wpService, $acfService, $postToSchemaAdapterCommandHelpers);
$eventResponseModifier             = new EventResponseModifier($postToSchemaAdapter, $wpService);

$hooksRegistrar->register($eventResponseModifier);

/**
 * Clean up unused tags.
 */
$cleanUpUnusedTags = new CleanupUnusedTags('keyword', $wpService);

$hooksRegistrar->register($cleanUpUnusedTags);

/**
 * Set post terms from content.
 */
$config = [
    'eventPostType'        => 'event',
    'eventKeywordTaxonomy' => 'keyword'
];

$tagReader               = new TagReader($wpService);
$modifyPostcontentBefore = new \EventManager\Modifiers\ModifyPostContentBeforeReadingTags($wpService);
$setPostTermsFromContent = new SetPostTermsFromContent($config['eventPostType'], $config['eventKeywordTaxonomy'], $tagReader, $wpService);

$hooksRegistrar->register($setPostTermsFromContent);
$hooksRegistrar->register($modifyPostcontentBefore);

/**
 * Cron scheduler
 */
$cronScheduler = new CronScheduler($wpService);

$hooksRegistrar->register($cronScheduler);

/**
 * Expired events
 */

$cleanupExpiredEvents = $wpService->getOption('options_cleanup_cleanupExpiredEvents') === '1';

if ($cleanupExpiredEvents) {
    $deleteExpiredPostsAfter   = strtotime($wpService->getOption('options_cleanup_deleteExpiredPostsAfter', '-1 month'));
    $eventGracePeriodTimestamp = strtotime('-1 day');

    $eventsInGracePeriod      = new ExpiredEvents($eventGracePeriodTimestamp, $wpService, $acfService);
    $eventsReadyForDeletion   = new ExpiredEvents($deleteExpiredPostsAfter, $wpService, $acfService);
    $adminNotifyExpiredEvents = new \EventManager\ContentExpirationManagement\AdminNotifyExpiredPost([$eventsInGracePeriod], $wpService);
    $deleteExpiredEvents      = new \EventManager\ContentExpirationManagement\DeleteExpiredPosts([$eventsReadyForDeletion], $wpService);

    $cronScheduler->addEvent(new \EventManager\CronScheduler\CronEvent('daily', 'event_manager_delete_expired_events_cron', [$deleteExpiredEvents, 'delete']));

    $hooksRegistrar->register($adminNotifyExpiredEvents);
}

/**
 * Table columns.
 */
$organizationCellContent =  new TermNameCellContent('organization', $wpService);
$organizationCellSort    = new MetaStringSort('organization', $wpService);
$organizationColumn      = new PostTableColumn(__('Organizer', 'api-event-manager'), 'organization', $organizationCellContent, $organizationCellSort);

$locationCellContent =  new NestedMetaStringCellContent('location.address', $wpService, new GetNestedArrayStringValueRecursive());
$locationCellSort    = new NestedMetaStringSort('location.address', $wpService, new GetNestedArrayStringValueRecursive());
$locationColumn      = new PostTableColumn(__('Location', 'api-event-manager'), 'location.address', $locationCellContent, $locationCellSort);

$postTableColumnsManager = new \EventManager\PostTableColumns\Manager(['event'], $wpService);
$postTableColumnsManager->register($organizationColumn);
$postTableColumnsManager->register($locationColumn);

$hooksRegistrar->register($postTableColumnsManager);

/**
 * Disable gutenberg editor
 */
$disableGutenbergEditor = new \EventManager\Modifiers\DisableGutenbergEditor($wpService);
$hideUnusedAdminPages   = new \EventManager\HideUnusedAdminPages($wpService);

$hooksRegistrar->register($disableGutenbergEditor);
$hooksRegistrar->register($hideUnusedAdminPages);

/**
 * Post types
 */
$eventPostType = new \EventManager\PostTypes\Event($wpService, $acfService);

$hooksRegistrar->register($eventPostType);

/**
 * Taxonomies
 */
$audienceTaxonomy     = new \EventManager\Taxonomies\Audience($wpService);
$organizationTaxonomy = new \EventManager\Taxonomies\Organization($wpService);
$keywordTaxonomy      = new \EventManager\Taxonomies\Keyword($wpService);

$hooksRegistrar->register($audienceTaxonomy);
$hooksRegistrar->register($organizationTaxonomy);
$hooksRegistrar->register($keywordTaxonomy);

/**
 * Frontend form
 */
$frontendForm      = new \EventManager\Modules\FrontendForm\Register($wpService, $acfService);
$frontendFormStyle = new \EventManager\AssetRegistry\FrontEndFormStyle($wpService);

$hooksRegistrar->register($frontendForm);
$hooksRegistrar->register($frontendFormStyle);
