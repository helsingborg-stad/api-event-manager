<?php

namespace EventManager\Services\WPService\Traits;

use WP_Post;

trait GetPostParent
{
    public function getPostParent(int|WP_Post|null $postId): ?WP_Post
    {
        $parent = get_post_parent($postId);

        if (is_a($parent, WP_Post::class)) {
            return $parent;
        }

        return null;
    }
}
