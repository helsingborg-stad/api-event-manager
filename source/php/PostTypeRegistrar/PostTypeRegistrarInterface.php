<?php

namespace EventManager\PostTypeRegistrar;

use WP_Post_Type;

interface PostTypeRegistrarInterface
{
    public function register(string $name, array $args): void;
}
