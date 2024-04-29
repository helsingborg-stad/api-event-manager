<?php

namespace EventManager\User\UserHasCap\Implementations;

use AcfService\Contracts\GetField;
use EventManager\User\UserHasCap\Implementations\Helpers\IUsersBelongsToSameOrganization;
use EventManager\User\UserHasCap\UserHasCapInterface;
use WP_User;

class UserCanDeleteUser implements UserHasCapInterface
{
    public function __construct(private IUsersBelongsToSameOrganization $usersBelongsToSameOrganization)
    {
    }

    public function userHasCap(array $allcaps, array $caps, array $args, WP_User $user): array
    {
        if ($args[0] !== 'delete_user') {
            return $allcaps;
        }

        $userId = $args[2];

        if (in_array('delete_users', $user->roles)) {
            $allcaps['promote_users'] = true;
        }

        if (
            in_array('organization_administrator', $user->roles) &&
            $this->usersBelongsToSameOrganization->usersBelongsToSameOrganization($user->ID, $userId)
        ) {
            $allcaps['delete_users'] = true;
        }

        return $allcaps;
    }
}
