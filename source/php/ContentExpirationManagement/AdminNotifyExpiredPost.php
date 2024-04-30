<?php

namespace EventManager\ContentExpirationManagement;

use EventManager\HooksRegistrar\Hookable;
use WpService\Contracts\AddAction;
use WpService\Contracts\AdminNotice;
use WpService\Contracts\GetCurrentScreen;
use WpService\Contracts\GetTheId;

class AdminNotifyExpiredPost implements Hookable
{
    /**
     * Constructor.
     *
     * @param GetExpiredPostsInterface[] $expired An array of expired posts.
     * @param GetCurrentScreen&AdminNotice&GetTheId&AddAction $wpService The WPService instance.
     */
    public function __construct(
        private array $expired,
        private GetCurrentScreen&AdminNotice&GetTheId&AddAction $wpService
    ) {
    }

    public function addHooks(): void
    {
        $this->wpService->addAction('admin_notices', [$this, 'notify']);
    }

    public function notify(): void
    {
        $screen = $this->wpService->getCurrentScreen();

        if ($screen->base !== 'post') {
            return;
        }

        $currentId = $this->wpService->getTheId();
        $posts     = $this->getExpiredPostIds();

        if (in_array($currentId, $posts)) {
            $message = 'This event has passed and is marked for deletion.';
            $this->wpService->adminNotice($message, array( 'type' => 'warning' ));
        }
    }

    private function getExpiredPostIds(): array
    {
        $expiredPosts = array_map(fn($expired) => $expired->getExpiredPosts(), $this->expired);
        return array_merge(...$expiredPosts);
    }
}
