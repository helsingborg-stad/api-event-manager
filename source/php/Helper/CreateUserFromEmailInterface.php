<?php

namespace EventManager\Helper;

use WP_User;

interface CreateUserFromEmailInterface
{
    /**
     * Creates a new user with the given email address. If a user with the same email already exists, it will return the existing user.
     *
     * @param string $email The email address of the user to create.
     * @throws \Exception If the user creation fails for any reason.
     * @return WP_User The created or existing user object.
     */
    public function createUserFromEmail(string $email): WP_User;
}
