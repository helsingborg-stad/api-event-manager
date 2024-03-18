<?php

/**
 * Plugin Name:       Event Manager
 * Plugin URI:        https://github.com/helsingborg-stad/api-event-manager
 * Description:       Creates a api that may be used to manage events
 * Version: 2.10.0
 * Author:            Thor Brink @ Helsingborg Stad
 * Author URI:        https://github.com/helsingborg-stad
 * License:           MIT
 * License URI:       https://opensource.org/licenses/MIT
 * Text Domain:       api-event-manager
 * Domain Path:       /languages
 */

use EventManager\CleanupUnusedTags\CleanupUnusedTags;
use EventManager\Helper\HooksRegistrar;
use EventManager\Helper\HooksRegistrar\HooksRegistrarInterface;
use EventManager\Services\WPService\WPService;
use EventManager\Services\WPService\WPServiceFactory;
use EventManager\SetPostTermsFromContent\SetPostTermsFromContent;
use EventManager\TableColumns\PostTableColumns\OpenStreetMapTableColumn;
use EventManager\TableColumns\PostTableColumns\PostTableColumnsManager;
use EventManager\TableColumns\PostTableColumns\TermNameTableColumn;
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

/**
 * Configure application
 */
$diContainer = new \DI\Container();
$diContainer->set(HooksRegistrarInterface::class, \DI\create(HooksRegistrar::class));
$diContainer->set(WPService::class, WPServiceFactory::create());
$diContainer->set(TagReaderInterface::class, \DI\create(TagReader::class));

/**
 * Clean up unused tags.
 */
$diContainer->set(
    CleanupUnusedTags::class,
    \DI\autowire()
        ->constructorParameter('taxonomy', 'keyword')
        ->constructorParameter('wpService', \DI\get(WPService::class))
);

/**
 * Set post terms from content.
 */
$diContainer->set(
    SetPostTermsFromContent::class,
    \DI\autowire()
        ->constructorParameter('postType', 'event')
        ->constructorParameter('taxonomy', 'keyword')
        ->constructorParameter('tagReader', $diContainer->get(TagReaderInterface::class))
        ->constructorParameter('wpService', \DI\get(WPService::class))
);

/**
 * Register table columns manager.
 */
$diContainer->set(
    PostTableColumnsManager::class,
    DI\autowire()
        ->constructorParameter('postTypes', ['event'])
        ->constructorParameter('wpService', \DI\get(WPService::class))
        ->method('register', \DI\autowire(OpenStreetMapTableColumn::class)
            ->constructorParameter('header', __('Location', 'api-event-manager'))
            ->constructorParameter('metaKey', 'location')
            ->constructorParameter('wpService', \DI\get(WPService::class)))
        ->method('register', \DI\autowire(TermNameTableColumn::class)
            ->constructorParameter('header', __('Organizer', 'api-event-manager'))
            ->constructorParameter('taxonomy', 'organization')
            ->constructorParameter('wpService', \DI\get(WPService::class)))
);

$app = $diContainer->get(EventManager\App::class);
$app->registerHooks();
