<?php

namespace EventManager\Notifications\Content;

use EventManager\HooksRegistrar\Hookable;
use WP_Post;
use WpService\Contracts\AddFilter;

class PendingEventCreatedMessage implements Hookable
{
    public function __construct(private AddFilter $wpService)
    {
    }

    public function addHooks(): void
    {
        $this->wpService->addFilter(
            'EventManager\pendingEventCreatedNotificationMessage',
            [$this, 'getMessage'],
            10,
            1
        );
    }

    public function getMessage(WP_Post $post): string
    {
        return 'foo';
    }
}
