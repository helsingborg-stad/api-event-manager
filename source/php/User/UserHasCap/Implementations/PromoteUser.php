<?php

namespace EventManager\User\UserHasCap\Implementations;

use EventManager\User\UserHasCap\Implementations\Helpers\IUsersBelongsToSameOrganization;
use EventManager\User\UserHasCap\UserHasCapInterface;
use WP_User;

class PromoteUser implements UserHasCapInterface
{
    public function __construct(private IUsersBelongsToSameOrganization $usersBelongsToSameOrganization)
    {
    }

    public function userHasCap(array $allcaps, array $caps, array $args, WP_User $user): array
    {
        if ($args[0] !== 'promote_user') {
            return $allcaps;
        }

        $userId = $args[2] ?? 0;

        if (in_array('administrator', $user->roles)) {
            $allcaps['promote_user']  = true;
            $allcaps['promote_users'] = true;
            return $allcaps;
        }

        if ($this->usersBelongsToSameOrganization->usersBelongsToSameOrganization($user->ID, $userId)) {
            $allcaps['promote_user']  = true;
            $allcaps['promote_users'] = true;
        }

        return $allcaps;
    }
}
