<?php

namespace EventManager\Services\WPService;

use WP_Error;

interface DeleteTerm
{
    /**
     * Deletes a term from a specified taxonomy.
     *
     * @param int $term The ID of the term to delete.
     * @param string $taxonomy The taxonomy of the term.
     * @param array|string $args Optional. Additional arguments for deleting the term. Default is an empty array.
     * @return bool|int|WP_Error Returns true on success, the number of terms deleted, or a WP_Error object on failure.
     */
    public function deleteTerm(int $term, string $taxonomy, array|string $args = array()): bool|int|WP_Error;
}
