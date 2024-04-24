<?php

namespace EventManager\User;

use EventManager\Helper\Hookable;
use WpService\Contracts\AddAction;
use WpService\Contracts\AddRole;

class RoleRegistrar implements Hookable
{
    /**
     * @param Role[] $roles
     * @param AddRole&AddAction $wpService
     */
    public function __construct(
        private array $roles,
        private AddRole&AddAction $wpService
    ) {
    }

    public function addHooks(): void
    {
        $this->wpService->addAction('init', [$this, 'registerRoles']);
    }

    public function registerRoles()
    {
        foreach ($this->roles as $role) {
            $this->wpService->addRole($role->getRole(), $role->getName(), $role->getCapabilities());
        }
    }
}
