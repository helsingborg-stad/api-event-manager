<?php

namespace EventManager\Services\WPService;

use WP_Role;

interface AddRole
{
    public function addRole(string $role, string $displayName, array $capabilities): ?WP_Role;
}
