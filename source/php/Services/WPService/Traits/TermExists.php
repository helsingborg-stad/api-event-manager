<?php

namespace EventManager\Services\WPService\Traits;

use WP_Error;

trait TermExists
{
    /**
     * @param int|string $term
     * @param string $taxonomy
     * @param int $parent
     *
     * @return null|int|array
     */
    public function termExists(
        int|string $term,
        string $taxonomy = "",
        int $parentTerm = null
    ): null|int|array {
        return term_exists($term, $taxonomy, $parentTerm);
    }
}
