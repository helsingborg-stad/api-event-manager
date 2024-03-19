<?php

namespace EventManager\PostTableColumns\ColumnSorters;

use WP_Query;

class MetaStringSort implements ColumnSortInterface
{
    public function sort(string $columnIdentifier, WP_Query $query): WP_Query
    {
        $query->set('meta_key', $columnIdentifier);
        $query->set('orderby', 'meta_value');

        return $query;
    }
}
