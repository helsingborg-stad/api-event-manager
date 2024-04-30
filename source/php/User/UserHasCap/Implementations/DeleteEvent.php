<?php

namespace EventManager\User\UserHasCap\Implementations;

use EventManager\User\UserHasCap\Implementations\Helpers\IPostBelongsToSameOrganizationAsUser;
use EventManager\User\UserHasCap\UserHasCapInterface;
use WP_User;

class DeleteEvent implements UserHasCapInterface
{
    public function __construct(private IPostBelongsToSameOrganizationAsUser $postBelongsToSameOrganizationAsUser)
    {
    }

    public function userHasCap(array $allcaps, array $caps, array $args, WP_User $user): array
    {
        if (!isset($args[0]) || $args[0] !== 'delete_post' || $caps[0] !== 'delete_event') {
            return $allcaps;
        }

        if (in_array('administrator', $user->roles)) {
            $allcaps['delete_event'] = true;
            return $allcaps;
        }

        if (in_array('organization_administrator', $user->roles) || in_array('organization_member', $user->roles)) {
            if ($this->postBelongsToSameOrganizationAsUser->postBelongsToSameOrganizationTermAsUser($user->ID, $args[2])) {
                $allcaps['delete_event'] = true;
                return $allcaps;
            }
        }

        return $allcaps;
    }
}
