<?php

namespace EventManager\ContentExpirationManagement;

use EventManager\Services\WPService\AddAction;
use EventManager\Services\WPService\AdminNotice;
use EventManager\Services\WPService\GetCurrentScreen;
use EventManager\Services\WPService\GetTheId;
use Mockery;
use PHPUnit\Framework\TestCase;
use WP_Screen;

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
        $screen                      = Mockery::mock(WP_Screen::class);
        $screen->parent_base         = 'edit';
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
        $screen                      = Mockery::mock(WP_Screen::class);
        $screen->parent_base         = 'edit';
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
        $screen                      = Mockery::mock(WP_Screen::class);
        $screen->parent_base         = 'not-edit';
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

    private function getWpService(): GetCurrentScreen&AdminNotice&GetTheId&AddAction
    {
        return new class implements GetCurrentScreen, AdminNotice, GetTheId, AddAction {
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

            public function adminNotice(string $message, array $args): void
            {
                echo $message;
            }

            public function addAction(string $tag, callable $function_to_add, int $priority = 10, int $accepted_args = 1): bool
            {
                return true;
            }
        };
    }
}
