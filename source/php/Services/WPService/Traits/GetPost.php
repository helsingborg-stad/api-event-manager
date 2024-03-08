<?php

namespace EventManager\Services\WPService\Traits;

use WP_Post;

trait GetPost
{
    public function getPost(
        int|WP_Post|null $post = null,
        string $output = OBJECT,
        string $filter = "raw"
    ): WP_Post|array|null {
        return get_post($post, $output, $filter);
    }
}
