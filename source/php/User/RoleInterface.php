<?php

namespace EventManager\User;

interface RoleInterface
{
    public function getRole(): string;

    public function getName(): string;

    public function getCapabilities(): array;
}
