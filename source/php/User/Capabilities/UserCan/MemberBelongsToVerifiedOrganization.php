<?php

namespace EventManager\User\Capabilities\UserCan;

use AcfService\Contracts\GetField;

class MemberBelongsToVerifiedOrganization implements UserCanInterface
{
    public function __construct(private GetField $acfService)
    {
    }

    public function userCan(int $userId, mixed $args): bool
    {
        return $this->userBelongsToVerifiedOrganization($userId);
    }

    private function userBelongsToVerifiedOrganization(int $userId): bool
    {
        $userOrganizationTermIds = $this->acfService->getField('organizations', "user_{$userId}") ?? [];

        if (empty($userOrganizationTermIds)) {
            return false;
        }

        foreach ($userOrganizationTermIds as $termId) {
            if ($this->acfService->getField('verified', "organization_{$termId}") === true) {
                return true;
            }
        }

        return false;
    }
}
