<?php

namespace EventManager\Services\WPService\Traits;

use WP_Error;

trait WPInsertTerm
{
    public function wpInsertTerm(
        string $term,
        string $taxonomy = "",
        array $args = []
    ): array|WP_Error {
        return wp_insert_term($term, $taxonomy, $args);
    }
}
