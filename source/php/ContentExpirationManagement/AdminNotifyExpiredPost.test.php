<?php

namespace EventManager\ContentExpirationManagement;

use WpService\Contracts\AddAction;
use WpService\Contracts\AdminNotice;
use WpService\Contracts\GetCurrentScreen;
use WpService\Contracts\GetTheId;
use Mockery;
use PHPUnit\Framework\TestCase;
use WP_Screen;
use WpService\Contracts\WpAdminNotice;

class AdminNotifyExpiredPostTest extends TestCase
{
    /**
     * @testdox notify() prints notification for expired post
     */
    public function testRemoveDeletedExpiredposts()
    {
        $expired                     = [ $this->getExpiredPosts() ];
        $wpService                   = $this->getWpService();
        $adminNotifyExpiredPosts     = new AdminNotifyExpiredPost($expired, $wpService);
        $screen                      = new WP_Screen();
        $screen->base                = 'post';
        $wpService->getCurrentScreen = $screen;

        ob_start();

        $adminNotifyExpiredPosts->notify();

        $this->assertStringContainsString('This event has passed and is marked for deletion.', ob_get_clean());
    }

    /**
     * @testdox notify() does not print notification for non-expired post
     */
    public function testRemoveDeletedExpiredpostsNonExpired()
    {
        $expired                     = [ $this->getExpiredPosts() ];
        $wpService                   = $this->getWpService();
        $adminNotifyExpiredPosts     = new AdminNotifyExpiredPost($expired, $wpService);
        $screen                      = new WP_Screen();
        $screen->base                = 'post';
        $wpService->getCurrentScreen = $screen;

        ob_start();
        $wpService->getTheId = 2;
        $adminNotifyExpiredPosts->notify();

        $this->assertEmpty(ob_get_clean());
    }

    /**
     * @testdox notify() does not print notification for non-edit screen
     */
    public function testRemoveDeletedExpiredpostsNonEditScreen()
    {
        $expired                     = [ $this->getExpiredPosts() ];
        $wpService                   = $this->getWpService();
        $adminNotifyExpiredPosts     = new AdminNotifyExpiredPost($expired, $wpService);
        $screen                      = new WP_Screen();
        $screen->base                = 'foo';
        $wpService->getCurrentScreen = $screen;

        ob_start();
        $wpService->getTheId = 1;
        $adminNotifyExpiredPosts->notify();

        $this->assertEmpty(ob_get_clean());
    }

    private function getExpiredPosts(): GetExpiredPostsInterface
    {
        return new class implements GetExpiredPostsInterface {
            public function getExpiredPosts(): array
            {
                return [1];
            }
        };
    }

    private function getWpService(): GetCurrentScreen&WpAdminNotice&GetTheId&AddAction
    {
        return new class implements GetCurrentScreen, WpAdminNotice, GetTheId, AddAction {
            public $getTheId         = 1;
            public $getCurrentScreen = null;

            public function getCurrentScreen(): ?WP_Screen
            {
                return $this->getCurrentScreen;
            }

            public function getTheId(): int
            {
                return $this->getTheId;
            }

            public function wpAdminNotice(string $message, array $args = []): void
            {
                echo $message;
            }

            public function addAction(string $hookName, callable $callback, int $priority = 10, int $acceptedArgs = 1): true
            {
                return true;
            }
        };
    }
}
