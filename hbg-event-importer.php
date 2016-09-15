<?php

/**
 * Plugin Name:       Event Manager
 * Plugin URI:        http://github.com/helsingborg-stad/api-event-manager/
 * Description:       Manage events locally, and import from XCAP & CBIS.
 * Version:           1.0.0
 * Author:            Kristoffer Svanmark, Sebastian Thulin, Tommy Morberg
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

load_plugin_textdomain('hbg-event-importer', false, plugin_basename(dirname(__FILE__)) . '/languages');

require_once HBGEVENTIMPORTER_PATH . 'source/php/Vendor/Psr4ClassLoader.php';
require_once HBGEVENTIMPORTER_PATH . 'Public.php';

// Instantiate and register the autoloader
$loader = new HbgEventImporter\Vendor\Psr4ClassLoader();
$loader->addPrefix('HbgEventImporter', HBGEVENTIMPORTER_PATH);
$loader->addPrefix('HbgEventImporter', HBGEVENTIMPORTER_PATH . 'source/php/');
$loader->register();

// Create necessary database tables when plugin is activated
register_activation_hook(plugin_basename(__FILE__), '\HbgEventImporter\App::database_creation');

// Start application
new HbgEventImporter\App();
