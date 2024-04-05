<?php

namespace EventManager\Services\WPService;

interface GetUsers
{
    /**
     * Retrieves users based on the provided arguments.
     *
     * @param array $args The arguments to filter the users.
     * @return WP_User[] The array of users matching the provided arguments.
     */
    public function getUsers(array $args): array;
}
