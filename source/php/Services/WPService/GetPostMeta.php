<?php

namespace EventManager\Services\WPService;

interface GetPostMeta
{
    public function getPostMeta($postId, $key = '', $single = false): mixed;
}
