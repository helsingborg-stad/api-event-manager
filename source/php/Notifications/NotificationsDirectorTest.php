<?php

namespace EventManager\Notifications;

use PHPUnit\Framework\TestCase;

class NotificationsDirectorTest extends TestCase
{
    /**
     * @testdox getNotificationForNewOrganizationAdminUser() should return a Notification with the correct subject, message and recipients
     */
    public function testGetNotificationForNewOrganizationAdminUser(): void
    {
        $user             = new \WP_User();
        $user->user_login = 'testuser';
        $user->user_email = 'test@example.com';
        $config           = static::createConfig();

        $notificationsDirector = new NotificationsDirector($config);
        $notificationsDirector->sendNotificationForNewOrganizationAdminUser($user);

        $sentNotifications = $config->getNotificationSender()->sentNotifications;

        static::assertEquals('Test subject for new organization member', $sentNotifications[0]->getSubject());
        static::assertEquals('Test message for new organization member', $sentNotifications[0]->getMessage());
        static::assertEquals([$user->user_email], $sentNotifications[0]->getRecipients());
    }

    private static function createConfig(): NotificationsConfigInterface
    {
        $sender = new class implements NotificationSenderInterface {
            public array $sentNotifications = [];
            public function send(NotificationInterface $notification): void
            {
                $this->sentNotifications[] = $notification;
            }
        };

        return new class ($sender) implements NotificationsConfigInterface {
            public function __construct(private NotificationSenderInterface $notificationSender)
            {
                $this->notificationSender = $notificationSender;
            }

            public function getNotificationSender(): NotificationSenderInterface
            {
                return $this->notificationSender;
            }

            public function getNotificationSubjectForNewOrganizationAdminUser(): string
            {
                return 'Test subject for new organization member';
            }

            public function getNotificationMessageForNewOrganizationAdminUser(\WP_User $user): string
            {
                return 'Test message for new organization member';
            }
        };
    }
}
