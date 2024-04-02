<?php

namespace EventManager\ContentExpirationManagement;

use EventManager\Services\WPService\DeletePost;
use PHPUnit\Framework\TestCase;

class DeleteExpiredPostsTest extends TestCase
{
    /**
     * @testdox remove() deletes expired posts
     */
    public function testRemoveDeletedExpiredposts()
    {
        $wpService = $this->getWpService();
        $remover   = new DeleteExpiredPosts([$this->createExpiredPost(1)], $wpService);

        ob_start();
        $remover->delete();

        $this->assertEquals('Deleted post 1', ob_get_clean());
    }

    /**
     * @testdox remove() deletes no posts if none are expired
     */
    public function testRemoveDeletedNoExpiredPosts()
    {
        $wpService = $this->getWpService();
        $remover   = new DeleteExpiredPosts([$this->createExpiredPost()], $wpService);

        ob_start();
        $remover->delete();

        $this->assertEmpty(ob_get_clean());
    }

    private function createExpiredPost(?int $postId = null): GetExpiredPostsInterface
    {
        return new class ($postId) implements GetExpiredPostsInterface {
            public function __construct(private ?int $postId)
            {
            }

            public function getExpiredPosts(): array
            {
                return $this->postId ? [$this->postId] : [];
            }
        };
    }

    private function getWPService(): DeletePost
    {
        return new class implements DeletePost {
            public function deletePost(int $postId, bool $forceDelete): void
            {
                echo "Deleted post $postId";
            }
        };
    }
}
