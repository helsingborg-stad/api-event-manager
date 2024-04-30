<?php

namespace EventManager\NotificationServices;

use WpService\Contracts\GetUsers;
use WpService\Contracts\Mail;

class EmailNotificationService implements NotificationService
{
    public array $recipientEmails = [];
    private string $subject;
    private string $message;

    public function __construct(private Mail&GetUsers $wpService)
    {
    }

    /**
     * @inheritDoc
     */
    public function setRecipients(array $userIds): void
    {
        if (empty($userIds)) {
            return;
        }

        $recipientEmails = $this->wpService->getUsers([
            'include' => $userIds,
            'fields'  => 'user_email'
        ]);

        if (!empty($recipientEmails)) {
            $this->recipientEmails = $recipientEmails;
        }
    }

    public function setSubject(string $subject): void
    {
        $this->subject = $subject;
    }

    public function setMessage(string $message): void
    {
        $this->message = $message;
    }

    public function send(): void
    {
        if (!empty($this->recipientEmails)) {
            $this->wpService->mail($this->recipientEmails, $this->subject, $this->message);
        }
    }
}
