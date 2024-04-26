<?php

namespace EventManager\User\UserHasCap\Implementations\Helpers;

use AcfService\Contracts\GetField;
use WpService\Contracts\GetPostTerms;

class PostBelongsToSameOrganizationAsUser implements IPostBelongsToSameOrganizationAsUser
{
    public function __construct(private GetPostTerms $wpService, private GetField $acfService)
    {
    }

    public function postBelongsToSameOrganizationTermAsUser(int $userId, int $postId): bool
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
