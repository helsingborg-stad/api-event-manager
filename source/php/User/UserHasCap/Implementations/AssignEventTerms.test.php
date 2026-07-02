<?php

namespace EventManager\User\UserHasCap\Implementations;

use PHPUnit\Framework\TestCase;
use WP_User;

class AssignEventTermsTest extends TestCase
{
    /**
     * @testdox userHasCap() should allow given roles to assign event terms
     */
    public function testUserHasCapShouldAllowGivenRolesToAssignEventTerms()
    {
        $userCanAssignEventTerms = new AssignEventTerms();
        $allcaps                 = ['assign_event_terms' => false];
        $user                    = $this->createMock(WP_User::class);
        $user->method('has_cap')->willReturn(true);

        $result = $userCanAssignEventTerms->userHasCap($allcaps, [], ['assign_event_terms'], $user);

        $this->assertEquals(['assign_event_terms' => true], $result);
    }

    /**
     * @testdox userHasCap() should return the $allcaps array unchanged if the first argument is not 'assign_event_terms'
     */
    public function testUserHasCapShouldReturnTheAllcapsArrayUnchangedIfTheFirstArgumentIsNotAssignEventTerms()
    {
        $userCanAssignEventTerms = new AssignEventTerms();
        $allcaps                 = ['assign_event_terms' => false];

        $result = $userCanAssignEventTerms->userHasCap($allcaps, [], [], $this->createMock(WP_User::class));

        $this->assertEquals($allcaps, $result);
    }

    /**
     * @testdox userHasCap() should keep assign_event_terms denied when the user has no allowed role
     */
    public function testUserHasCapShouldKeepAssignEventTermsDeniedWhenTheUserHasNoAllowedRole()
    {
        $userCanAssignEventTerms = new AssignEventTerms();
        $allcaps                 = ['assign_event_terms' => false];
        $user                    = $this->createMock(WP_User::class);
        $user->method('has_cap')->willReturn(false);

        $result = $userCanAssignEventTerms->userHasCap($allcaps, [], ['assign_event_terms'], $user);

        $this->assertEquals($allcaps, $result);
    }
}
