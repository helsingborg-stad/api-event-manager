<?php

namespace EventManager\Services\WPService\Traits;

use WP_Error;

trait DeleteTerm
{
    /**
     * Deletes a term from a specified taxonomy.
     *
     * @param int $term The ID of the term to delete.
     * @param string $taxonomy The taxonomy of the term.
     * @param array|string $args Optional. Additional arguments for deleting the term. Default empty array.
     * @return bool|int|WP_Error Returns true on success, the number of affected rows, or a WP_Error object on failure.
     */
    public function deleteTerm(int $term, string $taxonomy, array|string $args = array()): bool|int|WP_Error
    {
        return wp_delete_term($term, $taxonomy, $args);
    }
}
