<?php

namespace EventManager\PostTableColumns\ColumnSorters;

use WP_Query;

class MetaStringSort implements ColumnSortInterface
{
    public function __construct(private string $metaKey)
    {
    }

    public function sort(WP_Query $query): WP_Query
    {
        $query->set('meta_key', $this->metaKey);
        $query->set('orderby', 'meta_value');

        return $query;
    }
}
