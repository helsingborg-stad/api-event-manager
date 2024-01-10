<?php

namespace EventManager\PostTypeRegistrar;

use WP_Post_Type;

class PostTypeRegistrar implements PostTypeRegistrarInterface
{
    public function register(string $name, array $args): void
    {
        register_post_type($name, $args);
    }
}
