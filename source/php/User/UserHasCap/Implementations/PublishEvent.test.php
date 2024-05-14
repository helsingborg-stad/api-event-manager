<?php

namespace EventManager\User\UserHasCap\Implementations;

use PHPUnit\Framework\TestCase;
use WP_User;

class PublishEventTest extends TestCase
{
    /**
     * @testdox userHasCap() should allow given roles to publish events
     * @dataProvider userRoleCapsDataProvider
     */
    public function testUserHasCapShouldAllowGivenRolesToPublishEvents($roles)
    {
        $userCanPublishEvent = new PublishEvent();
        $allcaps             = ['publish_events' => false];
        $user                = $this->createMock('WP_User');
        $user->roles         = $roles;

        $result = $userCanPublishEvent->userHasCap($allcaps, [], ['publish_events'], $user);

        $this->assertEquals(['publish_events' => true], $result);
    }

    public function userRoleCapsDataProvider(): array
    {
        return [
            [['administrator']],
            [['organization_administrator']],
            [['organization_member']],
        ];
    }

    /**
     * @testdox userHasCap() should not allow pending_organization_member to publish events
     */
    public function testUserHasCapShouldNotAllowPendingOrganizationMemberToPublishEvents()
    {
        $userCanPublishEvent = new PublishEvent();
        $allcaps             = ['publish_events' => false];
        $user                = $this->createMock('WP_User');
        $user->roles         = ['pending_organization_member'];

        $result = $userCanPublishEvent->userHasCap($allcaps, [], ['publish_events'], $user);

        $this->assertEquals(['publish_events' => false], $result);
    }
}
