<?php

namespace EventManager\Organizations;

use AcfService\Contracts\UpdateField;
use EventManager\Helper\CreateUserFromEmailInterface;
use WpService\Contracts\DoAction;
use WpService\Contracts\WpUpdateUser;

/**
 * Creates and connects an organization administrator user to an organization term.
 */
class CreateOrganizationAdminUser
{
    /**
     * @param WpUpdateUser&DoAction $wpService WordPress service abstractions.
     * @param UpdateField $acfService ACF service abstraction.
     * @param CreateUserFromEmailInterface $createUserFromEmail Creates a user from an email address.
     */
    public function __construct(
        private WpUpdateUser&DoAction $wpService,
        private UpdateField $acfService,
        private CreateUserFromEmailInterface $createUserFromEmail
    ) {
    }

    /**
     * Creates an organization administrator user and connects the user to the organization term.
     *
     * @param int $organizationId The organization term ID.
     * @param string $email The email address to create the user from.
     *
     * @return \WP_User The created user.
     */
    public function create(int $organizationId, string $email): \WP_User
    {
        $wpUser = $this->createUserFromEmail->createUserFromEmail($email);

        $wpUser->set_role('organization_administrator');
        $this->wpService->wpUpdateUser($wpUser);
        $this->acfService->updateField('organizations', [$organizationId], 'user_' . $wpUser->ID);
        $this->wpService->doAction('EventManager/OrganizationUserCreated', $wpUser);

        return $wpUser;
    }
}
