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

// Start application
new HbgEventImporter\App();
