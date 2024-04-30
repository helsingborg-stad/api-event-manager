<?php

namespace EventManager\User\UserHasCap\Implementations;

use PHPUnit\Framework\TestCase;

class UserCanCreateUsersTest extends TestCase
{
    /**
     * @testdox userHasCap() returns $allcaps with 'create_users' set to true if the user has an allowed role
     * @dataProvider hasCapProvider
     */
    public function testUserHasCapReturnsSameAllcapsArrayWhenRequestedCapabilityIsNotCreateUsers($hasCap): void
    {
        // Create a mock of the WP_User class
        $userMock = $this->getMockBuilder(\WP_User::class)->addMethods(['has_cap'])->getMock();

        $userMock
            ->method('has_cap')
            ->withConsecutive(['administrator'], ['organization_administrator'])
            ->willReturnOnConsecutiveCalls($hasCap[0], $hasCap[1]);

        // Create an instance of the class under test
        $userCanCreateUsers = new UserCanCreateUsers();

        // Call the method under test
        $result = $userCanCreateUsers->userHasCap([], [], ['create_users', 1], $userMock);

        // Assert the expected result
        $this->assertEquals(['create_users' => true], $result);
    }

    /**
     * @testdox userHasCap() returns unchanged $allcaps array when requested capability is not 'create_users'
     */
    public function testUserHasCapReturnsUnchangedAllcapsArrayWhenRequestedCapabilityIsNotCreateUsers(): void
    {
        // Create a mock of the WP_User class
        $userMock = $this->getMockBuilder(\WP_User::class)->getMock();

        // Create an instance of the class under test
        $userCanCreateUsers = new UserCanCreateUsers();

        // Call the method under test
        $result = $userCanCreateUsers->userHasCap([], [], ['not_create_users', 1], $userMock);

        // Assert the expected result
        $this->assertEquals([], $result);
    }

    public function hasCapProvider(): array
    {
        return [
            [[true, false]],
            [[false, true]],
        ];
    }
}
