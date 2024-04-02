<?php

namespace EventManager\Services\WPService;

interface DeletePost
{
    public function deletePost(int $postId, bool $forceDelete): void;
}
