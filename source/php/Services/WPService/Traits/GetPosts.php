<?php

namespace EventManager\Services\WPService\Traits;

trait GetPosts
{
    public function getPosts(?array $args): array
    {
                return get_posts($args);
    }
}
