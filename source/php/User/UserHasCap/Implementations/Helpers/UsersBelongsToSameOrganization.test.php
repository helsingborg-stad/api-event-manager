<?php

namespace EventManager\User\UserHasCap\Implementations\Helpers;

use AcfService\Contracts\GetField;
use PHPUnit\Framework\TestCase;

class UsersBelongsToSameOrganizationTest extends TestCase
{
    /**
     * @testdox usersBelongsToSameOrganization() returns true if users belong to the same organization.
     */
    public function testUsersBelongsToSameOrganizationReturnsTrueIfUsersBelongToTheSameOrganization()
    {
        $acfService                     = $this->getFakeAcfService();
        $usersBelongsToSameOrganization = new UsersBelongsToSameOrganization($acfService);

        $this->assertTrue($usersBelongsToSameOrganization->usersBelongsToSameOrganization(1, 2));
    }

    /**
     * @testdox usersBelongsToSameOrganization() returns false if users do not belong to the same organization.
     */
    public function testUsersBelongsToSameOrganizationReturnsFalseIfUsersDoNotBelongToTheSameOrganization()
    {
        $acfService                     = $this->getFakeAcfService();
        $usersBelongsToSameOrganization = new UsersBelongsToSameOrganization($acfService);

        $this->assertFalse($usersBelongsToSameOrganization->usersBelongsToSameOrganization(1, 3));
    }

    private function getFakeAcfService(): GetField
    {
        return new class implements GetField {
            public $db = ['user_1' => [1,2], 'user_2' => [1,2], 'user_3' => [3,4]];
            public function getField(
                string $selector,
                int|false|string $postId = false,
                bool $formatValue = true,
                bool $escapeHtml = false
            ) {
                return $this->db[$postId] ?? [];
            }
        };
    }
}
