<?php

namespace EventManager\User\UserHasCap\Implementations\Helpers;

use AcfService\Contracts\GetField;

class UsersBelongsToSameOrganization implements IUsersBelongsToSameOrganization
{
    public function __construct(private GetField $acfService)
    {
    }

    public function usersBelongsToSameOrganization(int $userId, int $otherUserId): bool
    {
        $userOrganizationIds      = $this->acfService->getField('organizations', 'user_' . $userId) ?? [];
        $otherUserOrganizationIds = $this->acfService->getField('organizations', 'user_' . $otherUserId) ?? [];

        foreach ($userOrganizationIds as $id) {
            if (in_array($id, $otherUserOrganizationIds)) {
                return true;
            }
        }


        return false;
    }
}
