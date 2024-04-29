<?php

namespace EventManager\AddPromoteUserToRoleCapability;

use EventManager\Helper\Hookable;
use WpService\Contracts\AddFilter;
use WpService\Contracts\ApplyFilters;
use WpService\Contracts\GetCurrentUser;

class AddPromoteUserToRoleCapability implements Hookable
{
    public function __construct(private AddFilter&ApplyFilters&GetCurrentUser $wpService)
    {
    }

    public function addHooks(): void
    {
        $this->wpService->addFilter('editable_roles', [$this, 'restrictEditableRoles']);
    }

    public function restrictEditableRoles(array $roles): array
    {
        $currentUser = $this->wpService->getCurrentUser();

        return array_filter($roles, function ($role) use ($currentUser) {
            return $currentUser->has_cap("promote_user_to_role", $role);
        }, ARRAY_FILTER_USE_KEY);
    }
}
