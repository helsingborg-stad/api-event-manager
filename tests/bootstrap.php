<?php

// First we need to load the composer autoloader, so we can use WP Mock

use tad\FunctionMocker\FunctionMocker;

require_once dirname(__DIR__) . '/vendor/autoload.php';

// Bootstrap Patchwork
WP_Mock::setUsePatchwork(true);

// Bootstrap WP_Mock to initialize built-in features
WP_Mock::bootstrap();

WP_Mock::userFunction('plugin_dir_path')->andReturn('./');
WP_Mock::userFunction('plugins_url')->andReturn('foo');
WP_Mock::userFunction('load_plugin_textdomain')->andReturn('foo');
WP_Mock::userFunction('plugin_basename')->andReturn('foo');
WP_Mock::userFunction('is_wp_error', [
    'return' => function ($object) {
        return $object instanceof WP_Error;
    }
]);

FunctionMocker::init([
    'include' => [dirname(__DIR__)]
]);

// Optional step
require_once dirname(__DIR__) . '/api-event-manager.php';
