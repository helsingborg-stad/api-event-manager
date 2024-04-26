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

        if ($this->userHasAppropriateRole($user)) {
            $allcaps['publish_events'] = true;
            return $allcaps;
        }

        return $allcaps;
    }

    private function userHasAppropriateRole(WP_User $user): bool
    {
        $allowedRoles = ['administrator', 'organization_administrator', 'organization_member'];
        return !empty(array_intersect($allowedRoles, $user->roles));
    }
}
