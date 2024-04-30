<?php

namespace EventManager\User\UserHasCap\Implementations\Helpers;

use AcfService\Contracts\GetField;

class UserBelongsToOrganization implements IUserBelongsToOrganization
{
    public function __construct(private GetField $acfService)
    {
    }

    public function userBelongsToOrganization(int $userId, int $organizationId): bool
    {
        $userOrganizations = $this->acfService->getField('organizations', 'user_' . $userId);

        return !empty($userOrganizations) && in_array($organizationId, $userOrganizations);
    }
}
