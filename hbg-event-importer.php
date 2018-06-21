<?php

/**
 * Plugin Name:       Event Manager
 * Plugin URI:        http://github.com/helsingborg-stad/api-event-manager/
 * Description:       Manage events locally, and import from XCAP & CBIS.
 * Version:           1.0.0
 * Author:            Kristoffer Svanmark, Sebastian Thulin, Tommy Morberg, Jonatan Hanson
 * Author URI:        http://www.helsingborg.se
 * License:           MIT
 * License URI:       https://opensource.org/licenses/MIT
 * Text Domain:       event-manager
 * Domain Path:       /languages
 */

 // Protect agains direct file access
if (! defined('WPINC')) {
    die;
}

// Require composer dependencies (autoloader)
if (file_exists(__DIR__ . '/vendor/autoload.php')) {
    require_once __DIR__ . '/vendor/autoload.php';
}

// Include vendor files
if (file_exists(dirname(ABSPATH) . '/vendor/autoload.php')) {
    require_once dirname(ABSPATH) . '/vendor/autoload.php';
}

define('HBGEVENTIMPORTER_PATH', plugin_dir_path(__FILE__));
define('HBGEVENTIMPORTER_URL', plugins_url('', __FILE__));
define('HBGEVENTIMPORTER_TEMPLATE_PATH', HBGEVENTIMPORTER_PATH . 'templates/');

load_plugin_textdomain('event-manager', false, plugin_basename(dirname(__FILE__)) . '/languages');

require_once HBGEVENTIMPORTER_PATH . 'source/php/Vendor/Psr4ClassLoader.php';
require_once HBGEVENTIMPORTER_PATH . 'source/php/Vendor/AcfExportManager.php';
require_once HBGEVENTIMPORTER_PATH . 'Public.php';

// Instantiate and register the autoloader
$loader = new HbgEventImporter\Vendor\Psr4ClassLoader();
$loader->addPrefix('HbgEventImporter', HBGEVENTIMPORTER_PATH);
$loader->addPrefix('HbgEventImporter', HBGEVENTIMPORTER_PATH . 'source/php/');
$loader->register();

// Acf auto import and export
$acfExportManager = new HelsingborgsStad\AcfExportManager();
$acfExportManager->setTextdomain('event-manager');
$acfExportManager->setExportFolder(HBGEVENTIMPORTER_PATH . 'source/php/AcfFields/');
$acfExportManager->autoExport(array(
    'guide-basic' => 'group_589497ca3741e',
    'guide-group-select' => 'group_589dd0fbd412e',
    'guide-group-settings' => 'group_589dcf7e047a8',
    'guide-group-notices' => 'group_58ab055a4b3b8',
    'guide-navigation' => 'group_5a27f69d7c9cc',
    'guide-content-type' => 'group_5a2940e03054a',
    'cbis' => 'group_5760fe97e3be1',
    'event-categories' => 'group_5889f976dfb2d',
    'event-manager-settings' => 'group_575fe32901927',
    'event' => 'group_57610ebadcee8',
    'groups' => 'group_5885f51260b61',
    'location' => 'group_57612f9baa78b',
    'package' => 'group_57c94757c2169',
    'publishing-groups' => 'group_585a4624de2e9',
    'sponsor' => 'group_57a9bf12ef1a3',
    'xcap' => 'group_56af507bbd485',
    'organizer' => 'group_591eefa30b5fa',
    'membership-card' => 'group_593938d452989',
    'point-property' => 'group_5a2958bfd292c',
    'transticket' => 'group_5ae188afeb965',
    'arcgis' => 'group_5af54f9481184',
    'internal_event' => 'group_5afecb1bf1b8d',
    'recommendation' => 'group_5b15284158289',
    'under-processing' => 'group_5b2b60ac1bb08'
));
$acfExportManager->import();

// Activation / deactivation hooks
register_activation_hook(plugin_basename(__FILE__), '\HbgEventImporter\Cron::addCronJob');
register_deactivation_hook(plugin_basename(__FILE__), '\HbgEventImporter\Cron::removeCronJob');
register_activation_hook(plugin_basename(__FILE__), '\HbgEventImporter\Admin\UserRoles::createUserRoles');
register_deactivation_hook(plugin_basename(__FILE__), '\HbgEventImporter\Admin\UserRoles::removeCustomUserRoles');

// Create necessary database tables when plugin is activated
register_activation_hook(plugin_basename(__FILE__), '\HbgEventImporter\App::initDatabaseTable');

// Start application
$apiEventManager = new HbgEventImporter\App();
