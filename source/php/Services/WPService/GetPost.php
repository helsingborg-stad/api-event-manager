<?php

namespace EventManager\Services\WPService;

use WP_Post;

interface GetPost
{
    public function getPost(
        int|WP_Post|null $post = null,
        string $output = OBJECT,
        string $filter = "raw"
    ): WP_Post|array|null;
}
