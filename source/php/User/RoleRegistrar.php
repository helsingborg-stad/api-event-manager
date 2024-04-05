<?php

namespace EventManager\User;

use EventManager\Services\WPService\AddRole;

class RoleRegistrar
{
    public function __construct(
        private array $roles,
        private AddRole $wpService
    ) {
    }

    public function registerRoles()
    {
        foreach ($this->roles as $role) {
            $this->wpService->addRole($role->getRole(), $role->getName(), $role->getCapabilities());
        }
    }
}
