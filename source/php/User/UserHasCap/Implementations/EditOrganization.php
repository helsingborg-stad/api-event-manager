<?php

namespace EventManager\User\UserHasCap\Implementations;

use EventManager\User\UserHasCap\Implementations\Helpers\IUserBelongsToOrganization;
use EventManager\User\UserHasCap\UserHasCapInterface;
use WP_User;

class EditOrganization implements UserHasCapInterface
{
    public function __construct(private IUserBelongsToOrganization $userBelongsToOrganization)
    {
    }

    public function userHasCap(array $allcaps, array $caps, array $args, WP_User $user): array
    {
        if ($args[0] !== 'edit_term') {
            return $allcaps;
        }

        if (in_array('administrator', $user->roles)) {
            $allcaps['edit_organizations'] = true;
            return $allcaps;
        }

        if (in_array('organization_administrator', $user->roles)) {
            if ($this->userBelongsToOrganization->userBelongsToOrganization($args[1], $args[2])) {
                $allcaps['edit_organizations'] = true;
                return $allcaps;
            }
        }

        return $allcaps;
    }
}
