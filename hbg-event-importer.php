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
    'group_58b6e40e5a8f4',
    'halloj' => 'group_58b80e0111556'
));
$acfExportManager->import();

// Activation / deactivation hooks
register_activation_hook(plugin_basename(__FILE__), '\HbgEventImporter\App::addCronJob');
register_deactivation_hook(plugin_basename(__FILE__), '\HbgEventImporter\App::removeCronJob');
register_activation_hook(plugin_basename(__FILE__), '\HbgEventImporter\Admin\UserRoles::createUserRoles');

// Create necessary database tables when plugin is activated
register_activation_hook(plugin_basename(__FILE__), '\HbgEventImporter\App::initDatabaseTable');

// Start application
$apiEventManager = new HbgEventImporter\App();

/*
add_action('init', function () use ($apiEventManager) {
    //new \HbgEventImporter\Parser\CbisEvent('http://api.cbis.citybreak.com/Products.asmx?wsdl', $apiEventManager->getCbisKeys()[0]);
    new \HbgEventImporter\Parser\Xcap($apiEventManager->getXcapKeys()[0]['xcap_api_url'], $apiEventManager->getXcapKeys()[0]);
});
*/

