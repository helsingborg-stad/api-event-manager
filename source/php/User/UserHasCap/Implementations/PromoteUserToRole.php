<?php

namespace EventManager\User\UserHasCap\Implementations;

use EventManager\User\UserHasCap\UserHasCapInterface;
use WP_User;

class PromoteUserToRole implements UserHasCapInterface
{
    public function __construct()
    {
    }

    public function userHasCap(array $allcaps, array $caps, array $args, WP_User $user): array
    {
        if ($args[0] !== 'promote_user_to_role') {
            return $allcaps;
        }

        if (in_array('administrator', $user->roles)) {
            $allcaps['promote_user_to_role'] = true;
            return $allcaps;
        }

        if (in_array('organization_administrator', $user->roles)) {
            $allowedRoles = [
                'pending_organization_member',
                'organization_member',
                'organization_administrator',
            ];

            if (in_array($args[2], $allowedRoles)) {
                $allcaps['promote_user_to_role'] = true;
            }
        }

        return $allcaps;
    }
}
