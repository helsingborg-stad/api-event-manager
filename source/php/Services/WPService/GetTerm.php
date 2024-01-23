<?php

namespace EventManager\Services\WPService;

use WP_Error;
use WP_Term;

interface GetTerm
{
    /**
     * @param int|object|WP_Term $term
     * @param string $taxonomy
     * @param string $output
     * @param string $filter
     *
     * @return WP_Term|array|WP_Error|null
     */
    public function getTerm(
        int|object $term,
        string $taxonomy = '',
        string $output = OBJECT,
        string $filter = 'raw'
    ): WP_Term|array|WP_Error|null;
}
