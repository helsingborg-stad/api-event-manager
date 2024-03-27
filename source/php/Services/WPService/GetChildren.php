<?php

namespace EventManager\Services\WPService;

interface GetChildren
{
    /**
     * Retrieves all children of the post parent ID.
     *
     * @global WP_Post $post Global post object.
     *
     * @param mixed  $args   Optional. User defined arguments for replacing the defaults. Default empty.
     * @param string $output Optional. The required return type. One of OBJECT, ARRAY_A, or ARRAY_N, which
     *                       correspond to a WP_Post object, an associative array, or a numeric array,
     *                       respectively. Default OBJECT.
     * @return WP_Post[]|array[]|int[] Array of post objects, arrays, or IDs, depending on `$output`.
     */
    public function getChildren(mixed $args = '', string $output = OBJECT): array;
}
