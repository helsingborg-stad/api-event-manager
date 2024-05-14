<?php

require_once dirname(__DIR__) . '/../vendor/autoload.php';

// Autoload wordpress classes from vendor directory
require_once dirname(__DIR__) . '/../vendor/johnpbloch/wordpress-core/wp-includes/class-wp-user.php';

// Bootstrap WP_Mock to initialize built-in features
WP_Mock::bootstrap();
