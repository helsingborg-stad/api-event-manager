<?php

namespace EventManager\User\UserHasCap\Implementations;

use EventManager\User\UserHasCap\UserHasCapInterface;
use PHPUnit\Framework\TestCase;
use WP_User;

class UserCanPublishEventTest extends TestCase
{
    /**
     * @testdox userHasCap() should allow given roles to publish events
     * @dataProvider userRoleDataProvider
     */
    public function testUserHasCapShouldAllowGivenRolesToPublishEvents($role)
    {
        $userCanPublishEvent = new UserCanPublishEvent();
        $allcaps             = ['publish_events' => false];
        $user                = $this->getUser();
        $user->roles         = [$role];

        $result = $userCanPublishEvent->userHasCap($allcaps, [], ['publish_events'], $user);

        $this->assertEquals(['publish_events' => true], $result);
    }

    public function userRoleDataProvider(): array
    {
        return [
            ['administrator'],
            ['organization_administrator'],
            ['organization_member'],
        ];
    }

    /**
     * @testdox userHasCap() should not allow pending_organization_member to publish events
     */
    public function testUserHasCapShouldNotAllowPendingOrganizationMemberToPublishEvents()
    {
        $userCanPublishEvent = new UserCanPublishEvent();
        $allcaps             = ['publish_events' => false];
        $user                = $this->getUser();
        $user->roles         = ['pending_organization_member'];

        $result = $userCanPublishEvent->userHasCap($allcaps, [], ['publish_events'], $user);

        $this->assertEquals(['publish_events' => false], $result);
    }

    private function getUser(): WP_User
    {
        $user = new WP_User();

        return $user;
    }
}
