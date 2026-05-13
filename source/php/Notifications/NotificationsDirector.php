<?php

namespace EventManager\Notifications;

class NotificationsDirector implements NotificationsDirectorInterface
{
    public function __construct(private NotificationsConfigInterface $config)
    {
    }

    public function sendNotificationForNewOrganizationAdminUser(\WP_User $user): void
    {
        $subject    = $this->config->getNotificationSubjectForNewOrganizationAdminUser();
        $message    = $this->config->getNotificationMessageForNewOrganizationAdminUser($user);
        $recipients = [$user->user_email];

        $this->config->getNotificationSender()->send(new Notification($subject, $message, $recipients));
    }
}
