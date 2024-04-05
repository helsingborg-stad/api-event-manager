<?php

namespace EventManager\User;

class Role implements RoleInterface
{
    public function __construct(
        private string $role,
        private string $name,
        private array $capabilities = []
    ) {
    }

    public function getRole(): string
    {
        return $this->role;
    }

    public function getName(): string
    {
        return $this->name;
    }

    public function getCapabilities(): array
    {
        return $this->capabilities;
    }
}
