<?php

namespace EventManager\Services\WPService\Traits;

use WP_Post;

trait GetThePostThumbnailUrl
{
    public function getThePostThumbnailUrl(int|WP_Post $postId, string|array $size = 'post-thumbnail'): string|false
    {
        return get_the_post_thumbnail_url($postId, $size);
    }
}
