<?php

namespace EventManager\Notifications;

use PHPUnit\Framework\TestCase;
use WpService\Contracts\WpMail;

class EmailNotificationSenderTest extends TestCase
{
    /**
     * @testdox send calls the wpService to send an email with the notification message and recipients
     */
    public function testSend(): void
    {
        $wpService    = static::createWpService();
        $subject      = 'Test Subject';
        $message      = 'This is a test notification.';
        $recipients   = ['foo@bar.com'];
        $notification = new Notification($subject, $message, $recipients);

        $emailNotificationSender = new EmailNotificationSender($wpService);
        $emailNotificationSender->send($notification);

        static::assertCount(1, $wpService->sentEmails, 'Expected one email to be sent');
        static::assertSame($message, $wpService->sentEmails[0]['message']);
        static::assertSame($subject, $wpService->sentEmails[0]['subject']);
        static::assertSame($recipients, $wpService->sentEmails[0]['to']);
    }

    private static function createWpService(): WpMail
    {
        return new class implements WpMail {
            public array $sentEmails = [];
            public function wpMail(string|array $to, string $subject, string $message, string|array $headers = '', string|array $attachments = []): bool
            {
                $this->sentEmails[] = [
                    'to'          => $to,
                    'subject'     => $subject,
                    'message'     => $message,
                    'headers'     => $headers,
                    'attachments' => $attachments
                ];

                return true;
            }
        };
    }
}
