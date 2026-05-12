<?php

namespace EventManager\Notifications;

use EventManager\HooksRegistrar\Hookable;
use WpService\Contracts\AddAction;
use WpService\Contracts\AddFilter;

class SendNotificationsOnHooks implements Hookable
{
    public function __construct(
        private AddAction&AddFilter $wpService,
        private NotificationsDirectorInterface $notificationsDirector
    ) {
    }

    public function addHooks(): void
    {
        $this->wpService->addAction('EventManager/OrganizationUserCreated', [$this->notificationsDirector, 'sendNotificationForNewOrganizationAdminUser'], 10, 1);
        $this->wpService->addFilter('wp_send_new_user_notification_to_user', [$this, 'preventSendingCoreNewUserNotificationToOrganizationAdminUser'], 10, 2);
    }

    public function preventSendingCoreNewUserNotificationToOrganizationAdminUser(bool $send, \WP_User $user): bool
    {
        if (in_array('organization_administrator', $user->roles, true)) {
            return false;
        }

        return $send;
    }
}
