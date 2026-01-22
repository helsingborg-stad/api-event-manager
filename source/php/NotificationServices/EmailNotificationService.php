<?php

namespace EventManager\NotificationServices;

use WpService\Contracts\GetUsers;
use WpService\Contracts\SanitizeTextField;
use WpService\Contracts\WpKsesPost;
use WpService\Contracts\WpMail;

class EmailNotificationService implements NotificationService
{
    public array $recipientEmails = [];
    private string $subject;
    private string $message;

    public function __construct(private WpMail&GetUsers&SanitizeTextField&WpKsesPost $wpService)
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
        $this->subject = $this->wpService->sanitizeTextField($subject);
    }

    public function setMessage(string $message): void
    {
        $this->message = $this->wpService->wpKsesPost($message);
    }

    public function send(): void
    {
        if (!empty($this->recipientEmails)) {
            $this->wpService->wpMail($this->recipientEmails, $this->subject, $this->message);
        }
    }
}
