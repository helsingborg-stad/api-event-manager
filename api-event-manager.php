<?php

/**
 * Plugin Name:       Event Manager
 * Plugin URI:        https://github.com/helsingborg-stad/api-event-manager
 * Description:       Creates a api that may be used to manage events
 * Version: 2.13.1
 * Author:            Thor Brink @ Helsingborg Stad
 * Author URI:        https://github.com/helsingborg-stad
 * License:           MIT
 * License URI:       https://opensource.org/licenses/MIT
 * Text Domain:       api-event-manager
 * Domain Path:       /languages
 */

use EventManager\CleanupUnusedTags\CleanupUnusedTags;
use EventManager\Helper\HooksRegistrar;
use EventManager\PostTableColumns\Column as PostTableColumn;
use EventManager\PostTableColumns\ColumnCellContent\NestedMetaStringCellContent;
use EventManager\PostTableColumns\ColumnCellContent\TermNameCellContent;
use EventManager\PostTableColumns\ColumnSorters\MetaStringSort;
use EventManager\PostTableColumns\ColumnSorters\NestedMetaStringSort;
use EventManager\PostTableColumns\Helpers\GetNestedArrayStringValueRecursive;
use EventManager\PostToSchema\Mappers\StringToEventSchemaMapper;
use EventManager\PostToSchema\PostToEventSchema\PostToEventSchema;
use EventManager\Services\AcfService\AcfServiceFactory;
use EventManager\Services\WPService\WPServiceFactory;
use EventManager\SetPostTermsFromContent\SetPostTermsFromContent;
use EventManager\TagReader\TagReader;
use EventManager\ApiResponseModifiers\EventResponseModifier;
use EventManager\ContentExpirationManagement\ExpiredEvents;
use EventManager\PostToSchema\PostToEventSchema\Commands\Helpers\CommandHelpers;

// Protect against direct file access
if (!defined('WPINC')) {
    die;
}

define('EVENT_MANAGER_PATH', plugin_dir_path(__FILE__));
define('EVENT_MANAGER_URL', plugins_url('', __FILE__));
define('EVENT_MANAGER_TEMPLATE_PATH', EVENT_MANAGER_PATH . 'templates/');

// Register the autoloader
if (file_exists(EVENT_MANAGER_PATH . 'vendor/autoload.php')) {
    require EVENT_MANAGER_PATH . '/vendor/autoload.php';
}

$hooksRegistrar = new HooksRegistrar();
$wpService      = WPServiceFactory::create();
$acfService     = AcfServiceFactory::create();

/**
 * Load text domain
 */
$loadTextDomain = new \EventManager\Helper\LoadTextDomain($wpService);

/**
 * Acf export manager
 */
$acfExportManager = new \EventManager\Helper\RegisterAcfExportManager($wpService);

/**
 * Convert REST api posts to event schemas.
 */
$stringToEventSchemaMapper         = new StringToEventSchemaMapper();
$postToSchemaAdapterCommandHelpers = new CommandHelpers();
$postToSchemaAdapter               = new PostToEventSchema($stringToEventSchemaMapper, $wpService, $acfService, $postToSchemaAdapterCommandHelpers);
$eventResponseModifier             = new EventResponseModifier($postToSchemaAdapter, $wpService);

/**
 * Clean up unused tags.
 */
$cleanUpUnusedTags = new CleanupUnusedTags('keyword', $wpService);

/**
 * Set post terms from content.
 */
$tagReader               = new TagReader($wpService);
$modifyPostcontentBefore = new \EventManager\Modifiers\ModifyPostContentBeforeReadingTags($wpService);
$setPostTermsFromContent = new SetPostTermsFromContent('event', 'keyword', $tagReader, $wpService);

/**
 * Expired events
 */
$eventGracePeriodTimestamp = strtotime('-1 day');
$eventDeleteAfterTimestamp = strtotime('-1 month');
$eventsInGracePeriod       = new ExpiredEvents($eventGracePeriodTimestamp, $wpService, $acfService);
$eventsReadyForDeletion    = new ExpiredEvents($eventDeleteAfterTimestamp, $wpService, $acfService);
$adminNotifyExpiredEvents  = new \EventManager\ContentExpirationManagement\AdminNotifyExpiredPost([$eventsInGracePeriod], $wpService);
$deleteExpiredEvents       = new \EventManager\ContentExpirationManagement\DeleteExpiredPosts([$eventsReadyForDeletion], $wpService);

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

/**
 * Disable gutenberg editor
 */
$disableGutenbergEditor = new \EventManager\Modifiers\DisableGutenbergEditor($wpService);
$hideUnusedAdminPages   = new \EventManager\HideUnusedAdminPages($wpService);

/**
 * Post types
 */
$eventPostType = new \EventManager\PostTypes\Event($wpService, $acfService);

/**
 * Taxonomies
 */
$audienceTaxonomy     = new \EventManager\Taxonomies\Audience($wpService);
$organizationTaxonomy = new \EventManager\Taxonomies\Organization($wpService);
$keywordTaxonomy      = new \EventManager\Taxonomies\Keyword($wpService);

/**
 * Frontend form
 */
$frontendForm = new \EventManager\Modules\FrontendForm\Register($wpService, $acfService);

/**
 * Register hooks.
 */
$hooksRegistrar->register($loadTextDomain);
$hooksRegistrar->register($acfExportManager);
$hooksRegistrar->register($eventResponseModifier);
$hooksRegistrar->register($cleanUpUnusedTags);
$hooksRegistrar->register($setPostTermsFromContent);
$hooksRegistrar->register($modifyPostcontentBefore);
$hooksRegistrar->register($adminNotifyExpiredEvents);
$hooksRegistrar->register($deleteExpiredEvents);
$hooksRegistrar->register($postTableColumnsManager);
$hooksRegistrar->register($disableGutenbergEditor);
$hooksRegistrar->register($hideUnusedAdminPages);
$hooksRegistrar->register($eventPostType);
$hooksRegistrar->register($audienceTaxonomy);
$hooksRegistrar->register($organizationTaxonomy);
$hooksRegistrar->register($keywordTaxonomy);
$hooksRegistrar->register($frontendForm);
