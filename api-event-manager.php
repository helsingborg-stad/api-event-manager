<?php

/**
 * Plugin Name:       Event Manager
 * Plugin URI:        https://github.com/helsingborg-stad/api-event-manager
 * Description:       Creates a api that may be used to manage events
 * Version: 3.10.4
 * Author:            Thor Brink, Sebastian Thulin @ Helsingborg Stad
 * Author URI:        https://github.com/helsingborg-stad
 * License:           MIT
 * License URI:       https://opensource.org/licenses/MIT
 * Text Domain:       api-event-manager
 * Domain Path:       /languages
 */

use AcfService\Implementations\NativeAcfService;
use EventManager\HooksRegistrar\HooksRegistrar;
use EventManager\App;
use EventManager\CronScheduler\CronScheduler;
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

$textDomain               = 'api-event-manager';
$hooksRegistrar           = new HooksRegistrar();
$acfService               = new NativeAcfService();
$manifestFileWpService    = new WpServiceLazyDecorator();
$urlFilePathResolver      = new UrlFilePathResolver($manifestFileWpService);
$baseFileSystem           = new BaseFileSystem();
$manifestFilePathResolver = new ManifestFilePathResolver(EVENT_MANAGER_PATH . "dist/manifest.json", $baseFileSystem, $manifestFileWpService, $urlFilePathResolver);
$wpService                = new FilePathResolvingWpService(new NativeWpService(), $manifestFilePathResolver);
$manifestFileWpService->setInner(new WpServiceWithTextDomain($wpService, $textDomain));

$cronScheduler = new CronScheduler($wpService);
$hooksRegistrar->register($cronScheduler);

$app = new App(
    'api-event-manager',
    $wpService,
    $acfService,
    $hooksRegistrar,
    $cronScheduler
);

$app->loadPluginTextDomain();
$app->setupAcfExportManager();
$app->setupPluginSettingsPage();
$app->convertRestApiPostsToSchemaObjects();
$app->setupCleanupUnusedTags();
$app->setPostTermsFromPostContent();
$app->cleanUpExpiredEvents();
$app->modifyAdminTablesColumns();
$app->disableGutenbergEditor();
$app->setupPostTypes();
$app->setupTaxonomies();
$app->setupUserRoles();
$app->setupUserCapabilities();
$app->setupFrontendForm();
$app->setupAcfFieldContentModifiers();
$app->setupAcfSavePostActions();
$app->setupFeatureToShowOrHideAcfFieldsOnFrontendAndInAdmin();
$app->setupNotifications();
