<?php

namespace EventManager\ContentExpirationManagement;

use EventManager\HooksRegistrar\Hookable;
use WpService\Contracts\DeletePost;

/**
 * Class DeleteExpiredPosts
 *
 * This class is responsible for deleting expired posts.
 */
class DeleteExpiredPosts
{
    /**
     * Constructor.
     *
     * @param GetExpiredPostsInterface[] $expired An array of expired posts.
     * @param DeletePost $wpService The WPService instance used for deleting posts.
     */
    public function __construct(private array $expired, private DeletePost $wpService)
    {
    }

    /**
     * Deletes expired posts.
     *
     * This method retrieves the IDs of expired posts and uses the WPService instance
     * to delete them.
     *
     * @return void
     */
    public function delete(): void
    {
        $expiredPostIds = $this->getExpiredPostIds();

        foreach ($expiredPostIds as $postId) {
            $this->wpService->deletePost($postId, true);
        }
    }

    /**
     * Get the IDs of expired posts.
     *
     * This method iterates over the array of expired posts and retrieves the IDs
     * of each expired post.
     *
     * @return int[] The array of expired post IDs.
     */
    private function getExpiredPostIds(): array
    {
        $expiredPosts = array_map(fn($expired) => $expired->getExpiredPosts(), $this->expired);
        return array_merge(...$expiredPosts); // Flatten array.
    }
}
