<?php

namespace EventManager\User\UserHasCap\Implementations;

use EventManager\User\UserHasCap\UserHasCapInterface;
use WP_User;

/**
 * Dynamically grants taxonomy term assignment for event taxonomies.
 */
class AssignEventTerms implements UserHasCapInterface
{
    /**
     * @var string[]
     */
    private array $allowedRoles = [
        'organization_administrator',
        'organization_member',
        'pending_organization_member',
        'administrator',
    ];

    /**
     * Filters user capabilities for event taxonomy term assignment.
     *
     * @param bool[]   $allcaps All capabilities for the current user.
     * @param string[] $caps    Primitive capabilities.
     * @param array    $args    Arguments passed to the capability check.
     * @param WP_User  $user    Current user.
     *
     * @return bool[]
     */
    public function userHasCap(array $allcaps, array $caps, array $args, WP_User $user): array
    {
        if (!isset($args[0]) || $args[0] !== 'assign_event_terms') {
            return $allcaps;
        }

        foreach ($this->allowedRoles as $role) {
            if ($user->has_cap($role)) {
                $allcaps['assign_event_terms'] = true;
                break;
            }
        }

        return $allcaps;
    }
}
