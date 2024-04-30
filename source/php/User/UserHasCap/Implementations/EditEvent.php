<?php

namespace EventManager\User\UserHasCap\Implementations;

use EventManager\User\UserHasCap\Implementations\Helpers\IPostBelongsToSameOrganizationAsUser;
use EventManager\User\UserHasCap\UserHasCapInterface;
use WP_User;
use WpService\Contracts\GetPost;

class EditEvent implements UserHasCapInterface
{
    public function __construct(
        private IPostBelongsToSameOrganizationAsUser $postBelongsToSameOrganizationAsUser,
        private GetPost $wpService
    ) {
    }

    public function userHasCap(array $allcaps, array $caps, array $args, WP_User $user): array
    {
        if (!isset($args[0]) || $args[0] !== 'edit_post' || $caps[0] !== 'edit_event') {
            return $allcaps;
        }

        if (get_post_status($args[2]) === 'auto-draft') {
            // If the post is an auto-draft, the user can edit it
            $allcaps['edit_event'] = true;
            return $allcaps;
        }

        if (in_array('administrator', $user->roles)) {
            // If the user is an admin, they can edit any event
            $allcaps['edit_event'] = true;
            return $allcaps;
        }

        if (in_array('organization_administrator', $user->roles) || in_array('organization_member', $user->roles)) {
            // If the user is an organization admin or member, they can only edit events that belong to their organization
            if ($this->postBelongsToSameOrganizationAsUser->postBelongsToSameOrganizationTermAsUser($user->ID, $args[2])) {
                $allcaps['edit_event'] = true;
                return $allcaps;
            }
        }

        if (in_array('pending_organization_member', $user->roles)) {
            // If the user is a pending member, they can only edit their own events that are pending
            $post = $this->wpService->getPost($args[2]);
            if ($post->post_author == $user->ID && $post->post_status === 'pending') {
                $allcaps['edit_event'] = true;
                return $allcaps;
            }
        }

        return $allcaps;
    }
}
