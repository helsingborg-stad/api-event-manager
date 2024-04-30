<?php

namespace EventManager\User\UserHasCap\Implementations;

use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;
use WP_User;

class CreateUsersTest extends TestCase
{
    /**
     * @testdox userHasCap() returns $allcaps with 'create_users' set to true if the user has an allowed role
     * @dataProvider hasCapProvider
     */
    public function testUserHasCapReturnsSameAllcapsArrayWhenRequestedCapabilityIsNotCreateUsers($hasCap): void
    {
        $userMock = $this->getUserMock();
        $userMock
            ->method('has_cap')
            ->withConsecutive(['administrator'], ['organization_administrator'])
            ->willReturnOnConsecutiveCalls($hasCap[0], $hasCap[1]);
        $userCanCreateUsers = new CreateUsers();

        $result = $userCanCreateUsers->userHasCap([], [], ['create_users', 1], $userMock);

        $this->assertEquals(['create_users' => true], $result);
    }

    /**
     * @testdox userHasCap() returns unchanged $allcaps array when requested capability is not 'create_users'
     */
    public function testUserHasCapReturnsUnchangedAllcapsArrayWhenRequestedCapabilityIsNotCreateUsers(): void
    {
        $userMock           = $this->getUserMock();
        $userCanCreateUsers = new CreateUsers();

        $result = $userCanCreateUsers->userHasCap([], [], ['not_create_users', 1], $userMock);

        $this->assertEquals([], $result);
    }

    private function getUserMock(): WP_User|MockObject
    {
        return $userMock = $this->getMockBuilder(\WP_User::class)->addMethods(['has_cap'])->getMock();
    }

    public function hasCapProvider(): array
    {
        return [
            [[true, false]],
            [[false, true]],
        ];
    }
}
