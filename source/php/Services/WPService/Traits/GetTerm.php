<?php

namespace EventManager\Services\WPService\Traits;

use WP_Error;
use WP_Term;

trait GetTerm
{
    public function getTerm(
        int|object $term,
        string $taxonomy = '',
        string $output = OBJECT,
        string $filter = 'raw'
    ): WP_Term|array|WP_Error|null {
        return get_term($term, $taxonomy, $output, $filter);
    }
}
