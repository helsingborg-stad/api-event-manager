<?php

namespace EventManager\Services\WPService;

use WP_Post;

interface GetPostParent
{
    public function getPostParent(int|WP_Post|null $postId): ?WP_Post;
}
