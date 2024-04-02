<?php

namespace EventManager\ContentExpirationManagement;

interface GetExpiredPostsInterface
{
    /**
     * Retrieves an array of expired posts.
     *
     * @return int[] An array of post ids for posts that have expired and are ready for removal.
     */
    public function getExpiredPosts(): array;
}
