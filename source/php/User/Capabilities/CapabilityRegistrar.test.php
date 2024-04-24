<?php

namespace EventManager\User\Capabilities;

use WpService\Contracts\AddFilter;
use PHPUnit\Framework\TestCase;
use WP_User;

class CapabilityRegistrarTest extends TestCase
{
    /**
     * filterUserCapabilities() should return an array with all capabilities set to true if the user has the capability.
     */
    public function testFilterUserCapabilitiesReturnsArrayWithAllCapabilitiesSetToTrueIfUserHasCapability()
    {
        $allCaps = [];
        $caps    = ['some_capability'];
        $args    = ['some_capability'];
        $user    = $this->getUser();

        $registrar = new CapabilityRegistrar([$this->getCapability()], $this->getWpService());
        $allCaps   = $registrar->filterUserCapabilities($allCaps, $caps, $args, $user);

        $this->assertTrue($allCaps['some_capability']);
    }

    private function getUser(): WP_User
    {
        /** @var WP_User $user */
        $user     = $this->getMockBuilder('WP_User')->getMock();
        $user->ID = 1;

        return $user;
    }

    private function getCapability(): CapabilityInterface
    {
        return new class implements CapabilityInterface {
            public function getName(): string
            {
                return 'some_capability';
            }

            public function userCan(int $userId, mixed $args): bool
            {
                return true;
            }
        };
    }

    private function getWpService(): AddFilter
    {
        return new class implements AddFilter {
            public function addFilter(
                string $tag,
                callable $function_to_add,
                int $priority = 10,
                int $accepted_args = 1
            ): bool {
                return true;
            }
        };
    }
}
