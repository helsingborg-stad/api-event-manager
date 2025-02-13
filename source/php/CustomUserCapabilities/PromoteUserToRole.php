<?php

namespace EventManager\CustomUserCapabilities;

use EventManager\HooksRegistrar\Hookable;
use WpService\Contracts\AddFilter;
use WpService\Contracts\ApplyFilters;
use WpService\Contracts\WpGetCurrentUser;

class PromoteUserToRole implements Hookable
{
    public function __construct(private AddFilter&ApplyFilters&WpGetCurrentUser $wpService)
    {
    }

    public function addHooks(): void
    {
        $this->wpService->addFilter('editable_roles', [$this, 'restrictEditableRoles']);
    }

    public function restrictEditableRoles(array $roles): array
    {
        $currentUser = $this->wpService->WpGetCurrentUser();

        return array_filter($roles, function ($role) use ($currentUser) {
            return $currentUser->has_cap("promote_user_to_role", $role);
        }, ARRAY_FILTER_USE_KEY);
    }
}
