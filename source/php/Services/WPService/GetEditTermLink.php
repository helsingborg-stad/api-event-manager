<?php

namespace EventManager\Services\WPService;

use WP_Term;

interface GetEditTermLink
{
    public function getEditTermLink(
        int|WP_Term $term,
        string $taxonomy = '',
        string $objectType = ''
    ): string|null;
}
