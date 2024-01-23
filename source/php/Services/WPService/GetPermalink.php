<?php

namespace EventManager\Services\WPService;

use WP_Post;

interface GetPermalink
{
    /**
     * Retrieves the permalink for a post of a custom post type.
     *
     * @param int|WP_Post $post Optional. Post ID or post object. Default is global `$post`.
     * @return string|false The post permalink.
     */
    public function getPermalink(int|WP_Post $post = null, bool $leavename = false): string|false;
}
