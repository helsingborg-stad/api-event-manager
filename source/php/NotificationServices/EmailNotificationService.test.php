<?php

namespace EventManager\NotificationServices;

use PHPUnit\Framework\TestCase;
use WpService\Contracts\GetUsers;
use WpService\Contracts\Mail;

class EmailNotificationServiceTest extends TestCase
{
    /**
     * @testdox sets recipients to user emails when user ids are provided.
     */
    public function testSetRecipientsSetsRecipientsToUserEmailsWhenUserIdsAreProvided(): void
    {
        $wpService          = $this->getWpService(['getUsers' => ['foo@bar.baz']]);
        $notificationSender = new EmailNotificationService($wpService);

        $notificationSender->setRecipients([1]);

        $this->assertEquals(['foo@bar.baz'], $notificationSender->recipientEmails);
    }

    /**
     * @testdox send() sends email if resipients and message are set.
     */
    public function testSendSendsEmailIfRecipientsAndMessageAreSet(): void
    {
        $wpService                           = $this->getWpService();
        $notificationSender                  = new EmailNotificationService($wpService);
        $notificationSender->recipientEmails = ['foo@bar.baz'];
        $notificationSender->setMessage('message');
        $notificationSender->setSubject('subject');

        $notificationSender->send();

        $this->assertEquals(['foo@bar.baz'], $wpService->calls['mail'][0][0]);
        $this->assertEquals('subject', $wpService->calls['mail'][0][1]);
        $this->assertEquals('message', $wpService->calls['mail'][0][2]);
    }

    private function getWpService(array $data = []): Mail&GetUsers
    {
        return new class ($data) implements Mail, GetUsers {
            public array $calls = [];

            public function __construct(private array $data)
            {
            }

            public function mail(
                string|array $to,
                string $subject,
                string $message,
                string|array $headers = '',
                string|array $attachments = array()
            ): bool {

                if (!isset($this->calls['mail'])) {
                    $this->calls['mail'] = [];
                }

                $this->calls['mail'][] = [$to, $subject, $message, $headers, $attachments];
                return true;
            }

            public function getUsers(array $args): array
            {
                return $this->data['getUsers'] ?? [];
            }
        };
    }
}
