<?php

namespace EventManager\User;

use EventManager\Helper\Hookable;
use EventManager\Services\WPService\AddRole;

class RoleRegistrar implements Hookable
{
    public function __construct(
        private array $roles,
        private AddRole $wpService
    ) {
    }

    public function addHooks(): void
    {
        add_action('init', [$this, 'registerRoles']);
    }

    public function registerRoles()
    {
        foreach ($this->roles as $role) {
            $this->wpService->addRole($role->getRole(), $role->getName(), $role->getCapabilities());
        }
    }
}
