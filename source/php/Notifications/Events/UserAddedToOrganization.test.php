<?php

namespace EventManager\Notifications\Events;

use PHPUnit\Framework\TestCase;
use WpService\Contracts\AddFilter;
use WpService\Contracts\DoAction;
use WpService\Contracts\GetUserMeta;

class UserAddedToOrganizationTest extends TestCase
{
    /**
     * @testdox triggers action 'EventManager\userAddedToOrganization' when user is added to an organization.
     */
    public function testUpdateUserMetadataTriggersActionUserAddedToOrganizationWhenUserIsAddedToAnOrganization(): void
    {
        $wpService = $this->getWpService();
        $sut       = new UserAddedToOrganization($wpService);

        $sut->updateUserMetadata(null, 1, 'organizations', [2]);

        $this->assertEquals('EventManager\userAddedToOrganization', $wpService->doActionCalls[0][0]);
        $this->assertEquals(1, $wpService->doActionCalls[0][1]);
        $this->assertEquals(2, $wpService->doActionCalls[0][2]);
    }

    /**
     * @testdox triggers action 'EventManager\userAddedToOrganization' with only new organization id.
     */
    public function testUpdateUserMetadataTriggersActionUserAddedToOrganizationWithOnlyNewOrganizationId(): void
    {
        $wpService = $this->getWpService(['getUserMeta' => [2]]);
        $sut       = new UserAddedToOrganization($wpService);

        $sut->updateUserMetadata(null, 1, 'organizations', [2, 3]);

        $this->assertEquals('EventManager\userAddedToOrganization', $wpService->doActionCalls[0][0]);
        $this->assertEquals(1, $wpService->doActionCalls[0][1]);
        $this->assertEquals(3, $wpService->doActionCalls[0][2]);
    }

    private function getWpService(array $data = []): AddFilter&DoAction&GetUserMeta
    {
        return new class ($data) implements AddFilter, DoAction, GetUserMeta {
            public array $doActionCalls = [];

            public function __construct(private array $data)
            {
            }

            public function addFilter(string $hookName, callable $callback, int $priority = 10, int $acceptedArgs = 1): true
            {
                return true;
            }

            public function doAction(string $hookName, mixed ...$arg): void
            {
                $this->doActionCalls[] = [$hookName, ...$arg];
            }

            public function getUserMeta(int $userId, string $key = '', bool $single = false): mixed
            {
                return $this->data['getUserMeta'] ?? null;
            }
        };
    }
}
