<?php

namespace EventManager\CreateUserWhenOrganizationCreated;

use AcfService\Contracts\UpdateField;
use EventManager\AcfSavePostActions\CreateNewOrganizerFromEventSubmit\OrganizerData\IOrganizerData;
use EventManager\HooksRegistrar\Hookable;
use WpService\Contracts\AddAction;
use WpService\Contracts\DoAction;
use WpService\Contracts\GetUserBy;
use WpService\Contracts\GetUserdata;
use WpService\Contracts\WpCreateUser;
use WpService\Contracts\WpGeneratePassword;
use WpService\Contracts\WpNewUserNotification;
use WpService\Contracts\WpUpdateUser;

class CreateUserWhenOrganizationCreated implements Hookable
{
    public function __construct(private AddAction&GetUserBy&WpCreateUser&WpGeneratePassword&GetUserdata&WpUpdateUser&DoAction $wpService, private UpdateField $acfService)
    {
    }

    public function addHooks(): void
    {
        $this->wpService->addAction('EventManager/OrganizationCreated', [$this, 'createUserForOrganization'], 10, 3);
    }

    /**
     * Callback for when an organization is created. Responsible for creating a user for the organization.
     *
     * @param int $postId The ID of the event post the organization is associated with.
     * @param int $termId The ID of the created organization term.
     * @param IOrganizerData[] $organizersData An array of organizer data objects that were created from the submitted fields.
     */
    public function createUserForOrganization(int $postId, int $termId, array $organizersData): void
    {
        if (empty($organizersData)) {
            return;
        }

        foreach ($organizersData as $organizerData) {
            $existingUser = $this->wpService->getUserBy('email', $organizerData->getEmail());

            if ($existingUser !== false) {
                continue;
            }

            $password = $this->wpService->wpGeneratePassword();
            $userId   = $this->wpService->wpCreateUser($organizerData->getContact(), $password, $organizerData->getEmail());

            if ($userId instanceof \WP_Error) {
                // Log error or handle it as needed. For now, we'll just skip creating this user.
                error_log('Error creating user for organizer ' . $organizerData->getName() . ': ' . $userId->get_error_message());
                continue;
            }

            // Set user role to organization_administrator
            $wpUser = $this->wpService->getUserdata($userId);
            $wpUser->set_role('organization_administrator');
            $this->wpService->wpUpdateUser($wpUser);
            $this->acfService->updateField('organizations', [$termId], 'user_' . $userId);

            $this->wpService->doAction('EventManager/OrganizationUserCreated', $wpUser);
        }
    }
}
