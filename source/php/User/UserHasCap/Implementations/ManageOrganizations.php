<?php

namespace EventManager\User\UserHasCap\Implementations;

use EventManager\User\UserHasCap\UserHasCapInterface;
use WP_User;

class ManageOrganizations implements UserHasCapInterface
{
    public function __construct()
    {
    }

    public function userHasCap(array $allcaps, array $caps, array $args, WP_User $user): array
    {
        if ($args[0] !== 'manage_organizations') {
            return $allcaps;
        }

        if (array_intersect(array( 'administrator', 'organization_administrator' ), $user->roles)) {
            $allcaps['manage_organizations'] = true;
            return $allcaps;
        }

        return $allcaps;
    }
}
