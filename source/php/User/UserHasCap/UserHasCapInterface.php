<?php

namespace EventManager\User\UserHasCap;

use WP_User;

interface UserHasCapInterface
{
    /**
     * Dynamically filter a user's capabilities.
     *
     * @param bool[]   $allcaps Array of key/value pairs where keys represent a capability name
     *                          and boolean values represent whether the user has that capability.
     * @param string[] $caps    Required primitive capabilities for the requested capability.
     * @param array    $args {
     *     Arguments that accompany the requested capability check.
     *
     *     @type string    $0 Requested capability.
     *     @type int       $1 Concerned user ID.
     *     @type mixed  ...$2 Optional second and further parameters, typically object ID.
     * }
     * @param WP_User  $user    The user object.
     * @return bool[] Filtered array of key/value pairs where keys represent a capability name.
     */
    public function userHasCap(array $allcaps, array $caps, array $args, WP_User $user): array;
}
