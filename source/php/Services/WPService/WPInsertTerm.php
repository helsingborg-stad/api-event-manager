<?php

namespace EventManager\Services\WPService;

use WP_Error;

interface WPInsertTerm
{
    public function wpInsertTerm(
        string $term,
        string $taxonomy = "",
        array $args = []
    ): array|WP_Error;
}
