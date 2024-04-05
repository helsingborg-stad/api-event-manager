<?php

namespace EventManager\User;

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

    public function getWpService(): AddRole
    {
        return new class implements AddRole {
            public array $registeredRoles = [];

            public function addRole(string $role, string $displayName, array $capabilities): ?WP_Role
            {
                $this->registeredRoles[] = [$role, $displayName, $capabilities];
                return null;
            }
        };
    }
}
