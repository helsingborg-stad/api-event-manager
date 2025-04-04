<?php

namespace EventManager\User\UserHasCap\Implementations\Helpers;

use AcfService\Contracts\GetField;
use WpService\Contracts\WpGetPostTerms;

class PostBelongsToSameOrganizationAsUser implements IPostBelongsToSameOrganizationAsUser
{
    public function __construct(private WpGetPostTerms $wpService, private GetField $acfService)
    {
    }

    public function postBelongsToSameOrganizationTermAsUser(int $userId, int $postId): bool
    {
        $postTerms               = $this->wpService->wpGetPostTerms($postId, 'organization') ?: [];
        $userOrganizationTermIds = $this->acfService->getField('organizations', "user_{$userId}") ?? [];

        if (empty($postTerms) || empty($userOrganizationTermIds)) {
            return false;
        }

        $matches = array_intersect(
            array_map(fn($term) => $term->term_id, $postTerms),
            $userOrganizationTermIds
        );

        return !empty($matches);
    }
}
