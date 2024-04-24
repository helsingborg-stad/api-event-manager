<?php

namespace EventManager\User;

use WpService\Contracts\AddAction;
use WpService\Contracts\GetUsers;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;
use stdClass;
use WP_User;

class SyncRoleCapabilitiesToExistingUsersTest extends TestCase
{
    /**
     * @testdox Synchronize role capabilities to existing users of that role
     */
    public function testSynchronizeRoleCapabilitiesToExistingUsersOfThatRole()
    {
        $capabilities                        = ['testCapability'];
        $role                                = new Role('testRole', 'Test Role', $capabilities);
        $user                                = $this->getUser();
        $wpService                           = $this->getWpService([$user]);
        $syncRoleCapabilitiesToExistingUsers = new SyncRoleCapabilitiesToExistingUsers([$role], $wpService);

        $user->expects($this->once())->method('add_cap')->with('testCapability');

        $syncRoleCapabilitiesToExistingUsers->sync();
    }

    private function getUser(): WP_User|MockObject
    {
        return $this->getMockBuilder(stdClass::class)->addMethods(['add_cap'])->getMock();
    }

    private function getWpService($users = []): GetUsers&AddAction
    {
        return new class ($users) implements GetUsers, AddAction {
            public function __construct(private array $users)
            {
            }

            public function addAction(
                string $tag,
                callable $function_to_add,
                int $priority = 10,
                int $accepted_args = 1
            ): bool {
                return true;
            }

            public function getUsers(array $args): array
            {
                return $this->users;
            }
        };
    }
}
