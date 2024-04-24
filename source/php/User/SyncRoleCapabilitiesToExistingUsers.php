<?php

namespace EventManager\User;

use EventManager\Helper\Hookable;
use WpService\Contracts\AddAction;
use WpService\Contracts\GetUsers;

/**
 * Synchronize role capabilities to existing users of that role.
 * Solves issue with capabilities not being added to existing users when a new capability is added to an existing role.
 */
class SyncRoleCapabilitiesToExistingUsers implements Hookable
{
    /**
     * @param Role[] $roles
     * @param GetUsers&AddAction $wpService
     */
    public function __construct(private array $roles, private GetUsers&AddAction $wpService)
    {
    }

    public function addHooks(): void
    {
        $this->wpService->addAction('init', [$this, 'sync']);
    }

    public function sync(): void
    {
        foreach ($this->roles as $role) {
            $users = $this->getUsersByRole($role->getRole());
            foreach ($users as $user) {
                foreach ($role->getCapabilities() as $capability) {
                    $user->add_cap($capability);
                }
            }
        }
    }

    private function getUsersByRole(string $role)
    {
        return $this->wpService->getUsers(['role' => $role]);
    }
}
