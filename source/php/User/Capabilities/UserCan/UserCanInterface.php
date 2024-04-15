<?php

namespace EventManager\User\Capabilities\UserCan;

interface UserCanInterface
{
    public function userCan(int $userId, mixed $args): bool;
}
