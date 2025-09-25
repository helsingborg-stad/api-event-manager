<?php

/**
 * Plugin Name:       Event Manager
 * Plugin URI:        https://github.com/helsingborg-stad/api-event-manager
 * Description:       Creates a api that may be used to manage events
 * Version: 3.11.5
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
use WpService\Implementations\NativeWpService;

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

$textDomain     = 'api-event-manager';
$hooksRegistrar = new HooksRegistrar();
$acfService     = new NativeAcfService();
$wpService      = new NativeWpService();

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
$app->setupCleanupUnusedTags();
$app->setPostTermsFromPostContent();
$app->cleanUpExpiredEvents();
$app->modifyAdminTablesColumns();
$app->disableGutenbergEditor();
$app->setupPostTypes();
$app->setupTaxonomies();
$app->setupUserRoles();
$app->setupUserCapabilities();
$app->setupAcfFieldContentModifiers();
$app->setupAcfSavePostActions();
$app->setupFeatureToShowOrHideAcfFieldsOnFrontendAndInAdmin();
$app->setupNotifications();
