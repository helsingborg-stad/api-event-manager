<?php

/**
 * Plugin Name:       Event Manager
 * Plugin URI:        https://github.com/helsingborg-stad/api-event-manager
 * Description:       Creates a api that may be used to manage events
 * Version: 2.8.4
 * Author:            Thor Brink @ Helsingborg Stad
 * Author URI:        https://github.com/helsingborg-stad
 * License:           MIT
 * License URI:       https://opensource.org/licenses/MIT
 * Text Domain:       api-event-manager
 * Domain Path:       /languages
 */

use EventManager\CleanupUnusedTags\CleanupUnusedTags;
use EventManager\Helper\DIContainer\DIContainerFactory;
use EventManager\Helper\HooksRegistrar;
use EventManager\Helper\HooksRegistrar\HooksRegistrarInterface;
use EventManager\Services\WPService\WPService;
use EventManager\Services\WPService\WPServiceFactory;
use EventManager\SetPostTermsFromContent\SetPostTermsFromContent;
use EventManager\TagReader\TagReader;
use EventManager\TagReader\TagReaderInterface;

// Protect agains direct file access
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

// Start application
$diContainer = DIContainerFactory::create();
$diContainer
    ->bind(HooksRegistrarInterface::class, new HooksRegistrar())
    ->bind(WPService::class, WPServiceFactory::create())
    ->bind(TagReaderInterface::class, new TagReader())
    ->bind(CleanupUnusedTags::class, new CleanupUnusedTags('keyword', $diContainer->get(WPService::class)))
    ->bind(SetPostTermsFromContent::class, new SetPostTermsFromContent(
        'event',
        'keyword',
        $diContainer->get(TagReaderInterface::class),
        $diContainer->get(WPService::class)
    ));

(new EventManager\App($diContainer, $diContainer->get(HooksRegistrarInterface::class)))->registerHooks();
