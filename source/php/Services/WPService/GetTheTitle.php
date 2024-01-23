<?php

namespace EventManager\Services\WPService;

use WP_Post;

interface GetTheTitle
{
    public function getTheTitle(int|WP_Post $post = 0): string;
}
