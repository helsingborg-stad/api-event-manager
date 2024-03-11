<?php

namespace EventManager\Services\WPService;

use WP_Error;

interface GetTerms
{
    /**
     * Get terms using the `get_terms` function.
     *
     * @param array|string $args       Optional. Arguments to retrieve terms. Default empty array.
     * @param array|string $deprecated Optional. Deprecated argument. Default empty string.
     * @return WP_Term[]|int[]|string[]|string|WP_Error
     *         Array of terms on success, string on failure, or WP_Error object.
     */
    public function getTerms(array|string $args = array(), array|string $deprecated = ""): array | string | WP_Error;
}
