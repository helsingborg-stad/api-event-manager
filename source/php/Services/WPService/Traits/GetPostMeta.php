<?php

namespace EventManager\Services\WPService\Traits;

trait GetPostMeta
{
    public function getPostMeta($postId, $key = '', $single = false): mixed
    {
                return get_post_meta($postId, $key, $single);
    }
}
