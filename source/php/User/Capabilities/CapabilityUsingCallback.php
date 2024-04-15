<?php

namespace EventManager\User\Capabilities;

use EventManager\User\Capabilities\UserCan\UserCanInterface;

class CapabilityUsingCallback implements CapabilityInterface
{
    public function __construct(private string $name, private UserCanInterface $userCan)
    {
    }

    public function getName(): string
    {
        return $this->name;
    }

    public function userCan(int $userId, mixed $args): bool
    {
        return $this->userCan->userCan($userId, $args);
    }
}
