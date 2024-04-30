<?php

namespace EventManager\User\UserHasCap\Implementations\Helpers;

interface IUsersBelongsToSameOrganization
{
    public function usersBelongsToSameOrganization(int $userId, int $otherUserId): bool;
}
