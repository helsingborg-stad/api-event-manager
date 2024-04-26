<?php

namespace EventManager\User\UserHasCap\Implementations;

use AcfService\Contracts\GetField;
use EventManager\User\UserHasCap\UserHasCapInterface;
use WP_User;
use WpService\Contracts\GetPostTerms;

class UserCanDeleteEvent implements UserHasCapInterface
{
    public function __construct(private GetPostTerms $wpService, private GetField $acfService)
    {
    }

    public function userHasCap(array $allcaps, array $caps, array $args, WP_User $user): array
    {
        if (!isset($args[0]) || $args[0] !== 'delete_post' || $caps[0] !== 'delete_event') {
            return $allcaps;
        }

        if (in_array('administrator', $user->roles)) {
            // If the user is an admin, they can delete any event
            $allcaps['delete_event'] = true;
            return $allcaps;
        }

        if (in_array('organization_administrator', $user->roles) || in_array('organization_member', $user->roles)) {
            // If the user is an organization admin or member, they can only edit events that belong to their organization
            if ($this->postBelongsToSameOrganizationTermAsUser($user->ID, $args[2])) {
                $allcaps['delete_event'] = true;
                return $allcaps;
            }
        }

        return $allcaps;
    }

    private function postBelongsToSameOrganizationTermAsUser(int $userId, int $postId): bool
    {
        $postTerms               = $this->wpService->getPostTerms($postId, 'organization') ?? [];
        $userOrganizationTermIds = $this->acfService->getField('organizations', "user_{$userId}") ?? [];

        if (empty($postTerms) || empty($userOrganizationTermIds)) {
            return false;
        }

        foreach ($postTerms as $postTerm) {
            if (in_array($postTerm->term_id, $userOrganizationTermIds)) {
                return true;
            }
        }

        return false;
    }
}
