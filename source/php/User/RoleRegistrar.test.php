<?php

namespace EventManager\User;

use EventManager\Services\WPService\AddAction;
use EventManager\Services\WPService\AddRole;
use PHPUnit\Framework\TestCase;
use WP_Role;

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

    public function getWpService(): AddRole&AddAction
    {
        return new class implements AddRole, AddAction {
            public array $registeredRoles = [];

            public function addAction(
                string $tag,
                callable $function_to_add,
                int $priority = 10,
                int $accepted_args = 1
            ): bool {
                return true;
            }

            public function addRole(string $role, string $displayName, array $capabilities): ?WP_Role
            {
                $this->registeredRoles[] = [$role, $displayName, $capabilities];
                return null;
            }
        };
    }
}
