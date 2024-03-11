<?php

namespace EventManager\Services\WPService;

use WP_Error;

interface WPSetPostTerms
{
    public function wpSetPostTerms(
        int $postId,
        string|array $terms = "",
        string $taxonomy = "post_tag",
        bool $append = false
    ): array|false|WP_Error;
}
