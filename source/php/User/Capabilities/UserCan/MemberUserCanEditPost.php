<?php

namespace EventManager\User\Capabilities\UserCan;

use AcfService\Contracts\GetField;
use WpService\Contracts\GetPostTerms;

class MemberUserCanEditPost implements UserCanInterface
{
    public function __construct(private GetPostTerms $wpService, private GetField $acfService)
    {
    }

    public function userCan(int $userId, mixed $args): bool
    {
        if (!is_array($args) || empty($args) || !is_int($args[0])) {
            return false;
        }

        return $this->postBelongsToSameOrganizationTermAsUser($userId, $args[0]);
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
