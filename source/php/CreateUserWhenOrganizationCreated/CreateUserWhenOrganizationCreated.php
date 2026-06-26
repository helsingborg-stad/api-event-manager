<?php

namespace EventManager\CreateUserWhenOrganizationCreated;

use AcfService\Contracts\UpdateField;
use EventManager\AcfSavePostActions\CreateNewOrganizerFromEventSubmit\OrganizerData\IOrganizerData;
use EventManager\Helper\CreateUserFromEmailInterface;
use EventManager\HooksRegistrar\Hookable;
use WpService\Contracts\AddAction;
use WpService\Contracts\DoAction;
use WpService\Contracts\WpUpdateUser;

class CreateUserWhenOrganizationCreated implements Hookable
{
    public function __construct(
        private AddAction&WpUpdateUser&DoAction $wpService,
        private UpdateField $acfService,
        private CreateUserFromEmailInterface $createUserFromEmail
    ) {
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
            try {
                $wpUser = $this->createUserFromEmail->createUserFromEmail($organizerData->getEmail());
            } catch (\Exception $e) {
                // Log error or handle it as needed. For now, we'll just skip creating this user.
                error_log('Error creating user for organizer ' . $organizerData->getName() . ': ' . $e->getMessage());
                continue;
            }

            $wpUser->set_role('organization_administrator');
            $this->wpService->wpUpdateUser($wpUser);
            $this->acfService->updateField('organizations', [$termId], 'user_' . $wpUser->ID);

            $this->wpService->doAction('EventManager/OrganizationUserCreated', $wpUser);
        }
    }
}
