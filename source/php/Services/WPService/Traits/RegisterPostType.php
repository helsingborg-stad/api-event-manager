<?php

namespace EventManager\Services\WPService\Traits;

trait RegisterPostType
{
    public function registerPostType(string $postType, array $args = []): void
    {
        register_post_type($postType, $args);
    }
}
