<?php

namespace EventManager\User\UserHasCap\Implementations;

use EventManager\User\UserHasCap\UserHasCapInterface;
use WP_User;

class UserCanPromoteUsers implements UserHasCapInterface
{
    public function __construct()
    {
    }

    public function userHasCap(array $allcaps, array $caps, array $args, WP_User $user): array
    {
        if ($args[0] !== 'promote_users') {
            return $allcaps;
        }

        if ($user->has_cap('administrator')) {
            $allcaps['promote_users'] = true;
            return $allcaps;
        }

        if ($user->has_cap('organization_administrator')) {
            $allcaps['promote_users'] = true;
        }

        return $allcaps;
    }
}
