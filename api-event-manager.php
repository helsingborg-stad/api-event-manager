<?php

/**
 * Plugin Name:       Event Manager
 * Plugin URI:        https://github.com/helsingborg-stad/api-event-manager
 * Description:       Creates a api that may be used to manage events
 * Version: 3.7.1
 * Author:            Thor Brink, Sebastian Thulin @ Helsingborg Stad
 * Author URI:        https://github.com/helsingborg-stad
 * License:           MIT
 * License URI:       https://opensource.org/licenses/MIT
 * Text Domain:       api-event-manager
 * Domain Path:       /languages
 */

use AcfService\Implementations\NativeAcfService;
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
use EventManager\SetPostTermsFromContent\SetPostTermsFromContent;
use EventManager\TagReader\TagReader;
use EventManager\ApiResponseModifiers\EventResponseModifier;
use EventManager\ContentExpirationManagement\ExpiredEvents;
use EventManager\CronScheduler\CronScheduler;
use EventManager\PostToSchema\PostToEventSchema\Commands\Helpers\CommandHelpers;
use WpService\FileSystem\BaseFileSystem;
use WpService\FileSystemResolvers\ManifestFilePathResolver;
use WpService\FileSystemResolvers\UrlFilePathResolver;
use WpService\Implementations\FilePathResolvingWpService;
use WpService\Implementations\NativeWpService;
use WpService\Implementations\WpServiceLazyDecorator;
use WpService\Implementations\WpServiceWithTextDomain;

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

$textDomain = 'api-event-manager';

$hooksRegistrar = new HooksRegistrar();

/**
 * AcfService
 */
$acfService = new NativeAcfService();

/**
 * WpService
 */
$manifestFileWpService    = new WpServiceLazyDecorator();
$urlFilePathResolver      = new UrlFilePathResolver($manifestFileWpService);
$baseFileSystem           = new BaseFileSystem();
$manifestFilePathResolver = new ManifestFilePathResolver(EVENT_MANAGER_PATH . "dist/manifest.json", $baseFileSystem, $manifestFileWpService, $urlFilePathResolver);
$wpService                = new FilePathResolvingWpService(new NativeWpService(), $manifestFilePathResolver);

$manifestFileWpService->setInner(new WpServiceWithTextDomain($wpService, $textDomain));

/**
 * Load text domain
 */
$loadTextDomain = new \EventManager\Helper\LoadTextDomain($textDomain, $wpService);
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
 * User roles
 */
$userRoles = [
    new \EventManager\User\Role('organization_administrator', 'Organization Administrator', ['read']),
    new \EventManager\User\Role('organization_member', 'Organization Member', ['read']),
    new \EventManager\User\Role('pending_organization_member', 'Pending Organization Member', ['read']),
];

$hooksRegistrar->register(new \EventManager\User\RoleRegistrar($userRoles, $wpService));

/**
 * User capabilities
 */
$postBelongsToSameOrganizationAsUser = new \EventManager\User\UserHasCap\Implementations\Helpers\PostBelongsToSameOrganizationAsUser($wpService, $acfService);
$usersBelongsToSameOrganization      = new \EventManager\User\UserHasCap\Implementations\Helpers\UsersBelongsToSameOrganization($acfService);
$userBelongsToOrganization           = new \EventManager\User\UserHasCap\Implementations\Helpers\UserBelongsToOrganization($acfService);

$capabilities = [
    new \EventManager\User\UserHasCap\Implementations\EditEvents(),
    new \EventManager\User\UserHasCap\Implementations\EditEvent($postBelongsToSameOrganizationAsUser, $wpService),
    new \EventManager\User\UserHasCap\Implementations\EditOthersEvents(),
    new \EventManager\User\UserHasCap\Implementations\PublishEvent(),
    new \EventManager\User\UserHasCap\Implementations\DeleteEvent($postBelongsToSameOrganizationAsUser),
    new \EventManager\User\UserHasCap\Implementations\ListUsers(),
    new \EventManager\User\UserHasCap\Implementations\EditUsers(),
    new \EventManager\User\UserHasCap\Implementations\EditUser(),
    new \EventManager\User\UserHasCap\Implementations\DeleteUser($usersBelongsToSameOrganization),
    new \EventManager\User\UserHasCap\Implementations\PromoteUsers(),
    new \EventManager\User\UserHasCap\Implementations\PromoteUser($usersBelongsToSameOrganization),
    new \EventManager\User\UserHasCap\Implementations\PromoteUserToRole(),
    new \EventManager\User\UserHasCap\Implementations\CreateUsers(),
    new \EventManager\User\UserHasCap\Implementations\ManageOrganizations(),
    new \EventManager\User\UserHasCap\Implementations\EditOrganizations(),
    new \EventManager\User\UserHasCap\Implementations\EditOrganization($userBelongsToOrganization),
];

$hooksRegistrar->register(new \EventManager\User\UserHasCap\Registrar($capabilities, $wpService));

/**
 * Custom User capabilities
 */
$hooksRegistrar->register(new \EventManager\CustomUserCapabilities\PromoteUserToRole($wpService));

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

/**
 * ACF Field content modifiers
 */
$acfFieldContentModifierRegistrar = new \EventManager\AcfFieldContentModifiers\Registrar([
    new \EventManager\AcfFieldContentModifiers\FilterAcfAudienceSelectField('field_65a52a6374b0c', $wpService),
    new \EventManager\AcfFieldContentModifiers\FilterAcfOrganizerSelectField('field_65a4f6af50302', $wpService, $acfService)
], $wpService);

$hooksRegistrar->register($acfFieldContentModifierRegistrar);

/**
 * Acf save post actions
 */
$acfSavepostRegistrar = new \EventManager\AcfSavePostActions\Registrar([
    new \EventManager\AcfSavePostActions\SetPostTermsFromField('organization', 'organization', $wpService, $acfService),
    new \EventManager\AcfSavePostActions\SetPostTermsFromField('audience', 'audience', $wpService, $acfService),
], $wpService);

$hooksRegistrar->register($acfSavepostRegistrar);

/**
 * Field setting hide public
 */
$fieldSettingHidePublic = new \EventManager\FieldSettingHidePublic($wpService, $acfService);
$fieldSettingHidePublic->addHooks();
$fieldSettingHidePrivate = new \EventManager\FieldSettingHidePrivate($wpService, $acfService);
$fieldSettingHidePrivate->addHooks();

/**
 * Pre get post modifiers
 */
$hooksRegistrar->register(
    new \EventManager\PreGetPostModifiers\LimitEventTableResultsByUserRole($wpService, $acfService)
);

/**
 * Pre get users modifiers
 */
$hooksRegistrar->register(
    new \EventManager\PreGetUsersModifiers\ListOnlyUsersFromSameOrganization($wpService, $acfService)
);

/**
 * Post table filters
 */
$eventPostStatusFilter = new \EventManager\PostTableFilters\EventPostStatusFilter($wpService);
$eventPostStatusFilter->hideViewsFromEventTable(); // Hide views from table to avoid confusion.
$postTableFiltersRegistrar = new \EventManager\PostTableFilters\Registrar([
    $eventPostStatusFilter
], $wpService);

$hooksRegistrar->register($postTableFiltersRegistrar);

/**
 * Notifications
 */
$userAddedToOrganizationEvent          = new \EventManager\Notifications\Events\UserAddedToOrganization($wpService);
$emailNotificationSender               = new \EventManager\NotificationServices\EmailNotificationService($wpService);
$memberAddedToOrganizationNotification = new \EventManager\Notifications\MemberAddedToOrganization($emailNotificationSender, $wpService);
$pendingEventCreatedNotification       = new \EventManager\Notifications\PendingEventCreated($emailNotificationSender, $wpService);

$hooksRegistrar->register($userAddedToOrganizationEvent);
$hooksRegistrar->register($memberAddedToOrganizationNotification);
$hooksRegistrar->register($pendingEventCreatedNotification);
