<?php

namespace EventManager\User\UserHasCap\Implementations;

use EventManager\User\UserHasCap\UserHasCapInterface;
use WP_User;

class UserCanPublishEvent implements UserHasCapInterface
{
    public function __construct()
    {
    }

    public function userHasCap(array $allcaps, array $caps, array $args, WP_User $user): array
    {
        if (!isset($args[0]) || $args[0] !== 'publish_events') {
            return $allcaps;
        }

        if (
            in_array('administrator', $user->roles) ||
            in_array('organization_administrator', $user->roles) ||
            in_array('organization_member', $user->roles)
        ) {
            $allcaps['publish_events'] = true;
            return $allcaps;
        }

        return $allcaps;
    }
}
