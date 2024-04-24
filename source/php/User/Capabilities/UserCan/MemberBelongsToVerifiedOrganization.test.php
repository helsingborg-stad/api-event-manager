<?php

namespace EventManager\User\Capabilities\UserCan;

use AcfService\Contracts\GetField;
use PHPUnit\Framework\TestCase;

class MemberBelongsToVerifiedOrganizationTest extends TestCase
{
    public function testReturnsTrueIfPostBelongsToSameOrganizationTermAsUser(): void
    {
        $acfService = $this->getAcfService([
            'getField' => [
                "user_123"         => ['organizations' => [321]],
                "organization_321" => [ 'verified' => true ]
            ]
        ]);

        $userCan = new MemberBelongsToVerifiedOrganization($acfService);

        $this->assertTrue($userCan->userCan(123, null));
    }

    private function getAcfService($args): GetField
    {
        return new class ($args) implements GetField {
            public function __construct(public array $args)
            {
            }
            public function getField(
                string $selector,
                int|false|string $postId = false,
                bool $formatValue = true,
                bool $escapeHtml = false
            ) {
                return $this->args['getField'][$postId][$selector];
            }
        };
    }
}
