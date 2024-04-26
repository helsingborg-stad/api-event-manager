<?php

namespace EventManager\User\UserHasCap\Implementations;

use EventManager\User\UserHasCap\UserHasCapInterface;
use WP_User;

class UserCanEditOthersEvents implements UserHasCapInterface
{
    public function __construct()
    {
    }

    public function userHasCap(array $allcaps, array $caps, array $args, WP_User $user): array
    {
        if (!isset($args[0]) || $args[0] !== 'edit_others_events') {
            return $allcaps;
        }

        if (
            in_array('administrator', $user->roles) ||
            in_array('organization_administrator', $user->roles) ||
            in_array('organization_member', $user->roles)
        ) {
            // If the user is an admin, they can edit any event
            $allcaps['edit_others_events'] = true;
            return $allcaps;
        }

        return $allcaps;
    }
}
