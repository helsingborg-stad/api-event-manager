<?php

namespace EventManager\User\UserHasCap\Implementations\Helpers;

interface IPostBelongsToSameOrganizationAsUser
{
    public function postBelongsToSameOrganizationTermAsUser(int $userId, int $postId): bool;
}
