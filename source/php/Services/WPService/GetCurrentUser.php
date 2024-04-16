<?php

namespace EventManager\Services\WPService;

use WP_User;

interface GetCurrentUser
{
    /**
     * Get the current user.
     *
     * @return WP_User
     */
    public function getCurrentUser(): WP_User;
}
