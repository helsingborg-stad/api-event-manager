<?php

namespace EventManager\Services\WPService\Traits;

use WP_Error;

trait WPSetPostTerms
{
    public function wpSetPostTerms(
        int $postId,
        string|array $terms = "",
        string $taxonomy = "post_tag",
        bool $append = false
    ): array|false|WP_Error {
        return wp_set_post_terms($postId, $terms, $taxonomy, $append);
    }
}
