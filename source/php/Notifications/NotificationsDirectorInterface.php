<?php

namespace EventManager\Notifications;

interface NotificationsDirectorInterface
{
    public function sendNotificationForNewOrganizationAdminUser(\WP_User $user): void;
}
