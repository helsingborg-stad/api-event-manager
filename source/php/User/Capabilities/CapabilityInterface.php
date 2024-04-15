<?php

namespace EventManager\User\Capabilities;

interface CapabilityInterface
{
    /**
     * Get the capability name.
     *
     * @return string
     */
    public function getName(): string;
    /**
     * Determine if the current user has the capability.
     *
     * @param int $userId User ID to check the capability for.
     * @param mixed $args Optional arguments to pass to the capability check, typically object ID.
     *
     * @return bool
     */
    public function userCan(int $userId, mixed $args): bool;
}
