<?php

namespace EventManager\Notifications;

class NotificationsConfig implements NotificationsConfigInterface
{
    public function __construct(private NotificationSenderInterface $notificationSender)
    {
    }

    public function getNotificationSender(): NotificationSenderInterface
    {
        return $this->notificationSender;
    }

    public function getNotificationSubjectForNewOrganizationAdminUser(): string
    {
        return 'Välkommen som arrangör i Helsingborgs evenemangskalender!';
    }

    public function getNotificationMessageForNewOrganizationAdminUser(): string
    {
        return 'Du har lagts till som administratör för din organisation. Du kan nu hantera din organisation och dess evenemang.';
    }
}
