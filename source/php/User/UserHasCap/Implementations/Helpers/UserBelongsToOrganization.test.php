<?php

namespace EventManager\User\UserHasCap\Implementations\Helpers;

use AcfService\Contracts\GetField;
use PHPUnit\Framework\TestCase;

class UserBelongsToOrganizationTest extends TestCase
{
    /**
     * @testdox userBelongsToOrganization returns true if the user belongs to the organization
     */
    public function testUserBelongsToOrganizationReturnsTrueIfTheUserBelongsToTheOrganization()
    {
        $sut = new UserBelongsToOrganization($this->getAcfService());

        $this->assertTrue($sut->userBelongsToOrganization(1, 1));
    }

    private function getAcfService(): GetField
    {
        return new class implements GetField {
            public function getField(
                string $selector,
                int|false|string $postId = false,
                bool $formatValue = true,
                bool $escapeHtml = false
            ) {
                return [
                    'user_1' => ['organizations' => [1]]
                ][$postId][$selector] ?? false;
            }
        };
    }
}
