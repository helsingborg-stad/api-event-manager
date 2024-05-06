<?php

namespace EventManager\Notifications;

use EventManager\HooksRegistrar\Hookable;
use EventManager\NotificationServices\NotificationService;
use WpService\Contracts\AddAction;
use WpService\Contracts\GetUserdata;
use WpService\Contracts\GetUsers;

class MemberAddedToOrganization implements Hookable
{
    public function __construct(
        private NotificationService $notificationSender,
        private GetUsers&AddAction&GetUserdata $wpService
    ) {
    }

    public function addHooks(): void
    {
        $this->wpService->addAction('EventManager\userAddedToOrganization', [$this, 'userAddedToOrganization'], 10, 5);
    }

    public function userAddedToOrganization(int $userId, int $organizationId): void
    {
        $user = $this->wpService->getUserdata($userId);

        if ($user === false || $user->roles[0] !== 'pending_organization_member') {
            return;
        }

        $recipientIds = $this->getRecipientIds($organizationId);

        if (empty($recipientIds)) {
            return;
        }

        $this->notificationSender->setRecipients($recipientIds);
        $this->notificationSender->setSubject('New user added to organization');
        $this->notificationSender->setMessage('A new user has been added to the organization');
        $this->notificationSender->send();
    }

    private function getRecipientIds(int $organizationId): array
    {
        return $this->wpService->getUsers([
            'role'       => 'organization_administrator',
            'fields'     => 'ID',
            'meta_query' => [
                [
                    'key'     => 'organizations',
                    'value'   => '"' . $organizationId . '"',
                    'compare' => 'LIKE'
                ]
            ]
        ]);
    }
}
