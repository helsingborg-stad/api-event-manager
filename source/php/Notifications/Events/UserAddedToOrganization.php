<?php

namespace EventManager\Notifications\Events;

use EventManager\HooksRegistrar\Hookable;
use WpService\Contracts\AddFilter;
use WpService\Contracts\DoAction;
use WpService\Contracts\GetUserMeta;

class UserAddedToOrganization implements Hookable
{
    public function __construct(private AddFilter&DoAction&GetUserMeta $wpService)
    {
    }

    public function addHooks(): void
    {
        $this->wpService->addFilter('update_user_metadata', [$this, 'updateUserMetadata'], 10, 4);
    }

    public function updateUserMetadata(
        null|bool $check,
        int $objectId,
        string $metaKey,
        mixed $metaValue
    ): null|bool {

        if (!$this->isValidMeta($metaKey, $metaValue)) {
            return $check;
        }

        $prevValue          = $this->wpService->getUserMeta($objectId, 'organizations', true);
        $newOrganizationIds = $this->getNewOrganizationIds($metaValue, $prevValue);

        if (empty($newOrganizationIds)) {
            return $check;
        }

        foreach ($newOrganizationIds as $organizationId) {
            $this->doAction($objectId, (int) $organizationId);
        }

        return $check;
    }

    private function isValidMeta(string $metaKey, mixed $metaValue): bool
    {
        return $metaKey === 'organizations' && is_array($metaValue) && !empty($metaValue);
    }

    private function getNewOrganizationIds(array $new, mixed $old): array
    {
        if (empty($old)) {
            return $new;
        }

        return array_diff($new, $old);
    }

    private function doAction(int $objectId, int $organizationId)
    {
        /**
         * This hook is fired once a user is registered and added to an organization at the same time.
         *
         * @param int $userId The ID of the user that was registered.
         * @param int $organizationId The organization ID the user was added to.
         */
        $this->wpService->doAction('EventManager\userAddedToOrganization', $objectId, $organizationId);
    }
}
