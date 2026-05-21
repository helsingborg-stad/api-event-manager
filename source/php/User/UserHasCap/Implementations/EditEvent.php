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

        $postId = $args[2];

        if (get_post_status($postId) === 'auto-draft') {
            // If the post is an auto-draft, the user can edit it
            $allcaps['edit_event'] = true;
            return $allcaps;
        }

        if (in_array('administrator', $user->roles)) {
            // If the user is an admin, they can edit any event
            $allcaps['edit_event'] = true;
            return $allcaps;
        }

        if ($this->userIsOrganizationAdmin($user) || $this->userIsOrganizationMember($user)) {
            if (
                $this->postBelongsToSameOrganizationAsUser->postBelongsToSameOrganizationTermAsUser(
                    $user->ID,
                    $postId
                )
            ) {
                $allcaps['edit_event'] = true;
                return $allcaps;
            }
        }

        if ($this->userIsOrganizationAdmin($user) || $this->userIsOrganizationMember($user)) {
            // If is draft that belongs to the same user, they can edit it.
            // Allows to attach media to the event before publishing it.
            $post = $this->wpService->getPost($postId);
            if ((int)$post->post_author == $user->ID && in_array($post->post_status, ['draft'])) {
                $allcaps['edit_event'] = true;
                return $allcaps;
            }
        }

        if ($this->userIsPendingOrganizationMember($user)) {
            // If the user is a pending member, they can only edit their own events that are pending
            $post = $this->wpService->getPost($postId);
            if ((int)$post->post_author == $user->ID && in_array($post->post_status, ['pending', 'draft'])) {
                $allcaps['edit_event'] = true;
                return $allcaps;
            }
        }

        return $allcaps;
    }

    private function userIsOrganizationAdmin(WP_User $user): bool
    {
        return in_array('organization_administrator', $user->roles);
    }

    private function userIsOrganizationMember(WP_User $user): bool
    {
        return in_array('organization_member', $user->roles);
    }

    private function userIsPendingOrganizationMember(WP_User $user): bool
    {
        return in_array('pending_organization_member', $user->roles);
    }
}
