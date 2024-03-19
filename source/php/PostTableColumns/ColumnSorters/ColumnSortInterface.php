<?php

namespace EventManager\PostTableColumns\ColumnSorters;

use WP_Query;

interface ColumnSortInterface
{
    public function sort(WP_Query $query): WP_Query;
}
