<?php

namespace EventManager\Notifications;

use WpService\Contracts\WpMail;

class EmailNotificationSender implements NotificationSenderInterface
{
    public function __construct(private WpMail $wpService)
    {
    }

    public function send(NotificationInterface $notification): void
    {
        $this->wpService->wpMail(
            $notification->getRecipients(),
            $notification->getSubject(),
            $notification->getMessage()
        );
    }
}
