<?php

namespace EventManager\CreateUserWhenOrganizationCreated;

use EventManager\AcfSavePostActions\CreateNewOrganizerFromEventSubmit\OrganizerData\IOrganizerData;
use EventManager\HooksRegistrar\Hookable;
use EventManager\Organizations\CreateOrganizationAdminUser;
use WpService\Contracts\AddAction;

class CreateUserWhenOrganizationCreated implements Hookable
{
    public function __construct(
        private AddAction $wpService,
        private CreateOrganizationAdminUser $createOrganizationAdminUser
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
                $this->createOrganizationAdminUser->create($termId, $organizerData->getEmail());
            } catch (\Exception $e) {
                // Log error or handle it as needed. For now, we'll just skip creating this user.
                error_log('Error creating user for organizer ' . $organizerData->getName() . ': ' . $e->getMessage());
                continue;
            }
        }
    }
}
