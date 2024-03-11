<?php

/**
 * Plugin Name:       Event Manager
 * Plugin URI:        https://github.com/helsingborg-stad/api-event-manager
 * Description:       Creates a api that may be used to manage events
 * Version: 2.7.0
 * Author:            Thor Brink @ Helsingborg Stad
 * Author URI:        https://github.com/helsingborg-stad
 * License:           MIT
 * License URI:       https://opensource.org/licenses/MIT
 * Text Domain:       api-event-manager
 * Domain Path:       /languages
 */

// Protect agains direct file access
if (!defined('WPINC')) {
    die;
}

define('EVENT_MANAGER_PATH', plugin_dir_path(__FILE__));
define('EVENT_MANAGER_URL', plugins_url('', __FILE__));
define('EVENT_MANAGER_TEMPLATE_PATH', EVENT_MANAGER_PATH . 'templates/');

require_once EVENT_MANAGER_PATH . 'Public.php';

// Register the autoloader
if (file_exists(EVENT_MANAGER_PATH . 'vendor/autoload.php')) {
    require EVENT_MANAGER_PATH . '/vendor/autoload.php';
}

$wpService = EventManager\Services\WPService\WPServiceFactory::create();

// Disable Gutenberg editor for all post types
$wpService->addFilter('use_block_editor_for_post_type', '__return_false');

// Acf auto import and export
$wpService->addAction('acf/init', function () {
    $acfExportManager = new \AcfExportManager\AcfExportManager();
    $acfExportManager->setTextdomain('api-event-manager');
    $acfExportManager->setExportFolder(EVENT_MANAGER_PATH . 'source/php/AcfFields/');
    $acfExportManager->autoExport(array(
        'event-fields'        => 'group_65a115157a046',
        'organization-fields' => 'group_65a4f5a847d62',
        'audience-fields'     => 'group_65ae1b865887a',
        'frontend-fields'     => 'group_65eebfb9c35a7'
    ));

    $acfExportManager->import();
});

// Start application
$hooksRegistrar = new EventManager\Helper\HooksRegistrar();
$app            = new EventManager\App($wpService);
$app->registerHooks($hooksRegistrar);

$wpService->addAction('plugins_loaded', function () {
    load_plugin_textdomain('api-event-manager', false, dirname(plugin_basename(__FILE__)) . '/languages');
});
