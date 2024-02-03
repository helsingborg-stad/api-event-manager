<?php

namespace EventManager\Services\WPService\Traits;

use WP_Post;

trait GetTheTitle
{
    public function getTheTitle(int|WP_Post $post = 0): string
    {
        return get_the_title($post);
    }
}
