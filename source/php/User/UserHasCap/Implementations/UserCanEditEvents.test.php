<?php

namespace EventManager\User\UserHasCap\Implementations;

use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;
use WP_User;

class UserCanEditEventsTest extends TestCase
{
    /**
     * @testdox userHasCap() should allow given roles to edit events
     */
    public function testUserHasCapShouldAllowGivenRolesToEditEvents()
    {
        $userCanEditEvents = new UserCanEditEvents();
        $allcaps           = ['edit_events' => false];
        $user              = $this->getUser();

        $result = $userCanEditEvents->userHasCap($allcaps, [], ['edit_events'], $user);

        $this->assertEquals(['edit_events' => true], $result);
    }

    private function getUser(): WP_User|MockObject
    {
        $user = $this->getMockBuilder(WP_User::class)->addMethods(['has_cap'])->getMock();
        $user->method('has_cap')->willReturn(true);

        return $user;
    }

    /**
     * @testdox userHasCap() should return the $allcaps array unchanged if the first argument is not 'edit_events'
     */
    public function testUserHasCapShouldReturnTheAllcapsArrayUnchangedIfTheFirstArgumentIsNotEditEvents()
    {
        $userCanEditEvents = new UserCanEditEvents();
        $allcaps           = ['edit_events' => false];

        $result = $userCanEditEvents->userHasCap($allcaps, [], [], $this->createStub(WP_User::class));

        $this->assertEquals($allcaps, $result);
    }
}
