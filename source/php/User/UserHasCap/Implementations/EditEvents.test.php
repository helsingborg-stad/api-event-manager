<?php

namespace EventManager\User\UserHasCap\Implementations;

use PHPUnit\Framework\TestCase;
use WP_User;

class EditEventsTest extends TestCase
{
    /**
     * @testdox userHasCap() should allow given roles to edit events
     */
    public function testUserHasCapShouldAllowGivenRolesToEditEvents()
    {
        $userCanEditEvents = new EditEvents();
        $allcaps           = ['edit_events' => false];
        $user              = $this->createMock(WP_User::class);
        $user->method('has_cap')->willReturn(true);

        $result = $userCanEditEvents->userHasCap($allcaps, [], ['edit_events'], $user);

        $this->assertEquals(['edit_events' => true], $result);
    }

    /**
     * @testdox userHasCap() should return the $allcaps array unchanged if the first argument is not 'edit_events'
     */
    public function testUserHasCapShouldReturnTheAllcapsArrayUnchangedIfTheFirstArgumentIsNotEditEvents()
    {
        $userCanEditEvents = new EditEvents();
        $allcaps           = ['edit_events' => false];

        $result = $userCanEditEvents->userHasCap($allcaps, [], [], $this->createMock(WP_User::class));

        $this->assertEquals($allcaps, $result);
    }
}
