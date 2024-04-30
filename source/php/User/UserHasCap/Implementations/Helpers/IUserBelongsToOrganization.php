<?php

namespace EventManager\User\UserHasCap\Implementations\Helpers;

interface IUserBelongsToOrganization
{
    public function userBelongsToOrganization(int $userId, int $organizationId): bool;
}
