<?php

namespace EventManager\User\UserHasCap\Implementations;

use EventManager\User\UserHasCap\UserHasCapInterface;
use WP_User;

class CreateUsers implements UserHasCapInterface
{
    public function __construct()
    {
    }

    public function userHasCap(array $allcaps, array $caps, array $args, WP_User $user): array
    {
        if ($args[0] !== 'create_users') {
            return $allcaps;
        }

        if (
            $user->has_cap('administrator') ||
            $user->has_cap('organization_administrator')
        ) {
            $allcaps['create_users'] = true;
        }


        return $allcaps;
    }
}
