<?php

namespace EventManager\User;

use WpService\Contracts\AddAction;
use WpService\Contracts\AddRole;
use PHPUnit\Framework\TestCase;
use WP_Role;
use WpService\Contracts\GetRole;

class RoleRegistrarTest extends TestCase
{
    /**
     * @testdox registerRoles() registers roles with WordPress
     */
    public function testRegisterRolesRegistersRolesWithWordPress()
    {
        $wpService     = $this->getWpService();
        $role          = new Role('testRole', 'Test Role', []);
        $roleRegistrar = new RoleRegistrar([$role], $wpService);
        $roleRegistrar->registerRoles();

        $this->assertCount(1, $wpService->registeredRoles);
        $this->assertContains(['testRole', 'Test Role', []], $wpService->registeredRoles);
    }

    public function getWpService(): AddRole&AddAction&GetRole
    {
        return new class implements AddRole, AddAction, GetRole {
            public array $registeredRoles = [];

            public function addAction(string $hookName, callable $callback, int $priority = 10, int $acceptedArgs = 1): true
            {
                return true;
            }

            public function addRole(string $role, string $displayName, array $capabilities = []): mixed
            {
                $this->registeredRoles[$role] = [$role, $displayName, $capabilities];
                return null;
            }

            public function getRole(string $role): ?WP_Role
            {
                if(isset($this->registeredRoles[$role])) {
                    $wpRole = new WP_Role(...$this->registeredRoles[$role]);
                    $wpRole->capabilities = $this->registeredRoles[$role][2];
                    return $wpRole;
                }

                return null;
            }
        };
    }
}
