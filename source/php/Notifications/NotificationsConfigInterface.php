<?php

namespace EventManager\Notifications;

interface NotificationsConfigInterface
{
    public function getNotificationSender(): NotificationSenderInterface;
    public function getNotificationSubjectForNewOrganizationAdminUser(): string;
    public function getNotificationMessageForNewOrganizationAdminUser(\WP_User $user): string;
}
