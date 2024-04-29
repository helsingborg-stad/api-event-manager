<?php

namespace EventManager\User\UserHasCap\Implementations;

use EventManager\User\UserHasCap\UserHasCapInterface;
use WP_User;

class UserCanListUsers implements UserHasCapInterface
{
    public function __construct()
    {
    }

    public function userHasCap(array $allcaps, array $caps, array $args, WP_User $user): array
    {
        if (!isset($args[0]) || $args[0] !== 'list_users') {
            return $allcaps;
        }

        if ($this->userHasMatchingRole($user)) {
            $allcaps['list_users'] = true;
        }

        return $allcaps;
    }

    private function userHasMatchingRole(WP_User $user): bool
    {
        $matchingRoles = ['administrator', 'organization_administrator'];
        return array_intersect($matchingRoles, $user->roles) !== [];
    }
}
