<?php

namespace EventManager\ContentExpirationManagement;

use WpService\Contracts\DeletePost;
use PHPUnit\Framework\TestCase;
use WP_Post;
use WpService\Contracts\WpDeletePost;

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

    private function getWPService(): WpDeletePost
    {
        return new class implements WpDeletePost {
            public function wpDeletePost(int $postId = 0, bool $forceDelete = false): WP_Post|false|null
            {
                echo "Deleted post $postId";
                return new WP_Post([]);
            }
        };
    }
}
