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
use EventManager\PostTableColumns\Column as PostTableColumn;
use EventManager\PostTableColumns\ColumnCellContent\MetaStringCellContent;
use EventManager\PostTableColumns\ColumnCellContent\NestedMetaStringCellContent;
use EventManager\PostTableColumns\ColumnCellContent\TermNameCellContent;
use EventManager\PostTableColumns\ColumnSorters\MetaStringSort;
use EventManager\PostTableColumns\ColumnSorters\NestedMetaStringSort;
use EventManager\PostTableColumns\Helpers\GetNestedArrayStringValueRecursive;
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
        ->constructorParameter('tagReader', \DI\get(TagReaderInterface::class))
        ->constructorParameter('wpService', \DI\get(WPService::class))
);

/**
 * Register table columns.
 */
$postTableColumnsManageer = new \EventManager\PostTableColumns\Manager(['event'], $diContainer->get(WPService::class));
$wpService                = $diContainer->get(WPService::class);
$aboutColumn              = new PostTableColumn(__('About', 'api-event-manager'), 'about', new MetaStringCellContent($wpService), new MetaStringSort($wpService));
$organizationColumn       = new PostTableColumn(__('Organizer', 'api-event-manager'), 'organization', new TermNameCellContent($wpService), new MetaStringSort($wpService));
$locationColumn           = new PostTableColumn(__('Location', 'api-event-manager'), 'location.address', new NestedMetaStringCellContent($wpService, new GetNestedArrayStringValueRecursive()), new NestedMetaStringSort($wpService, new GetNestedArrayStringValueRecursive()));
$postTableColumnsManageer->register($aboutColumn);
$postTableColumnsManageer->register($organizationColumn);
$postTableColumnsManageer->register($locationColumn);

$diContainer->set(
    \EventManager\PostTableColumns\Manager::class,
    $postTableColumnsManageer
);

/**
 * Initialize application
 */
$app = $diContainer->get(EventManager\App::class);
$app->registerHooks();
