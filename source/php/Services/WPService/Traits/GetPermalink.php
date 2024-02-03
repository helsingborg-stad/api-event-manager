<?php

namespace EventManager\Services\WPService\Traits;

use WP_Post;

trait GetPermalink
{
    public function getPermalink(int|WP_Post $post = null, bool $leavename = false): string|false
    {
        return get_permalink($post, $leavename);
    }
}
