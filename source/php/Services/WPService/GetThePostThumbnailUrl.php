<?php

namespace EventManager\Services\WPService;

use WP_Post;

interface GetThePostThumbnailUrl
{
    /**
     * Returns the post thumbnail URL.
     *
     * @param int|WP_Post  $post Optional. Post ID or WP_Post object.  Default is global `$post`.
     * @param string|int[] $size Optional. Registered image size to retrieve the source for or a flat array
     *                           of height and width dimensions. Default 'post-thumbnail'.
     * @return string|false Post thumbnail URL or false if no image is available. If `$size` does not match
     *                      any registered image size, the original image URL will be returned.
     */
    public function getThePostThumbnailUrl(int|WP_Post $postId, string|array $size = 'post-thumbnail'): string|false;
}
