<?php

namespace EventManager\Services\WPService;

use WP_Error;

interface InsertTerm
{
    public function insertTerm(
        string $term,
        string $taxonomy = "",
        array $args = []
    ): array|WP_Error;
}
