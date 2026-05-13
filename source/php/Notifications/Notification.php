<?php

namespace EventManager\Notifications;

class Notification implements NotificationInterface
{
    public function __construct(
        private string $subject,
        private string $message,
        private array $recipients
    ) {
    }

    public function getSubject(): string
    {
        return $this->subject;
    }

    public function getMessage(): string
    {
        return $this->message;
    }

    public function getRecipients(): array
    {
        return $this->recipients;
    }
}
