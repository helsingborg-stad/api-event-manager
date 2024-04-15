<?php

namespace EventManager\User\Capabilities;

use EventManager\User\Capabilities\CapabilityCheck\CapabilityCheckInterface;
use EventManager\User\Capabilities\UserCan\UserCanInterface;
use PHPUnit\Framework\TestCase;

class CapabilityUsingCallbackTest extends TestCase
{
    public function testGetName()
    {
        $capability = new CapabilityUsingCallback('capabilityName', $this->getUserCan());
        $this->assertEquals('capabilityName', $capability->getName());
    }

    /**
     * @testdox userCan() returns invokes the callback and returns its result
     */
    public function testCallbackIsInvokedWithArguments()
    {

        $userCan    = $this->getUserCan();
        $capability = new CapabilityUsingCallback('capabilityName', $userCan);
        $result     = $capability->userCan(1, ['arg1']);

        $this->assertTrue($result);
        $this->assertEquals([1, ['arg1']], $userCan->invokations[0]);
    }

    private function getUserCan(): UserCanInterface
    {
        return new class implements UserCanInterface {
            public array $invokations = [];
            public function userCan(?int $userId = null, mixed $args): bool
            {
                $this->invokations[] = [$userId, $args];
                return true;
            }
        };
    }
}
