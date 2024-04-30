<?php

namespace EventManager\User\UserHasCap\Implementations;

use EventManager\User\UserHasCap\UserHasCapInterface;
use WP_User;

class EditEvents implements UserHasCapInterface
{
    private array $allowedRoles = [
        'organization_administrator',
        'organization_member',
        'pending_organization_member',
        'administrator'
    ];

    public function userHasCap(array $allcaps, array $caps, array $args, WP_User $user): array
    {
        if (!isset($args[0]) || $args[0] !== 'edit_events') {
            return $allcaps;
        }

        foreach ($this->allowedRoles as $role) {
            if ($user->has_cap($role)) {
                $allcaps['edit_events'] = true;
                break;
            }
        }

        return $allcaps;
    }
}
