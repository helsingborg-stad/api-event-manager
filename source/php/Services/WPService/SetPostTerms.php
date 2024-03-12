<?php

namespace EventManager\Services\WPService;

use WP_Error;

interface SetPostTerms
{
    public function setPostTerms(
        int $postId,
        string|array $terms = "",
        string $taxonomy = "post_tag",
        bool $append = false
    ): array|false|WP_Error;
}
