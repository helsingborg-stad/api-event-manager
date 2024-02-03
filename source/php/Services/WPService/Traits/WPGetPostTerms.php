<?php

namespace EventManager\Services\WPService\Traits;

use WP_Error;

trait WPGetPostTerms
{
    public function wpGetPostTerms(
        int $post_id,
        string|array $taxonomy = 'post_tag',
        array $args = array()
    ): array|WP_Error {
        return wp_get_post_terms($post_id, $taxonomy, $args);
    }
}
