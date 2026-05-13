<?php

namespace EventManager\Notifications;

use PHPUnit\Framework\TestCase;

class NotificationTest extends TestCase
{
    /**
     * @testdox getSubject returns the provided subject
     */
    public function testGetSubject(): void
    {
        $subject      = 'Test Subject';
        $notification = new Notification($subject, '', []);

        static::assertSame($subject, $notification->getSubject());
    }

    /**
     * @testdox getMessage returns the provided message
     */
    public function testGetMessage(): void
    {
        $message      = 'This is a test notification.';
        $notification = new Notification('', $message, []);

        static::assertSame($message, $notification->getMessage());
    }

    /**
     * @testdox getRecipients returns the provided recipients
     */
    public function testGetRecipients(): void
    {
        $recipients = ['foo@bar.com'];

        $notification = new Notification('', 'Test message', $recipients);

        static::assertSame($recipients, $notification->getRecipients());
    }
}
