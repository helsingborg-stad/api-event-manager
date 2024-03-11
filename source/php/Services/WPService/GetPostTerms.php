<?php

namespace EventManager\Services\WPService;

use WP_Error;
use WP_Term;

interface GetPostTerms
{
    /**
     * Get all terms for a given post.
     *
     * @param int $post_id
     * @param string|string[] $taxonomy
     * @param array $args
     *
     * @return WP_Term[]|WP_Error
     */
    public function getPostTerms(
        int $post_id,
        string|array $taxonomy = 'post_tag',
        array $args = array()
    ): array|WP_Error;
}
