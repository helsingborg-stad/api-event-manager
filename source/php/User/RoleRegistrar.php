<?php

namespace EventManager\User;

use EventManager\HooksRegistrar\Hookable;
use WpService\Contracts\AddAction;
use WpService\Contracts\AddRole;
use WpService\Contracts\GetRole;

class RoleRegistrar implements Hookable
{
    /**
     * @param Role[] $roles
     * @param AddRole&AddAction&GetRole $wpService
     */
    public function __construct(
        private array $roles,
        private AddRole&AddAction&GetRole $wpService
    ) {
    }

    public function addHooks(): void
    {
        $this->wpService->addAction('init', [$this, 'registerRoles']);
    }

    public function registerRoles()
    {
        foreach ($this->roles as $role) {
            $wpRole = $this->wpService->addRole($role->getRole(), $role->getName(), $role->getCapabilities());

            if(!is_null($wpRole) ) {
                continue;
            }

            // Role already exists, ensure that caopabilities are up to date
            $wpRole = $this->wpService->getRole($role->getRole());
            
            foreach ($role->getCapabilities() as $capability) {
                
                if($wpRole->has_cap($capability)) {
                    continue;
                }

                $wpRole->add_cap($capability);
            }

            // Remove any capabilities that are not in the role definition
            $existingCapabilities = $wpRole->capabilities;

            foreach ($existingCapabilities as $capability) {

                if (!in_array($capability, $role->getCapabilities())) {
                    $wpRole->remove_cap($capability);
                }
            }
        }
    }
}
