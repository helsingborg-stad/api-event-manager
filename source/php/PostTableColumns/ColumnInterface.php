<?php

namespace EventManager\PostTableColumns;

use WP_Query;

interface ColumnInterface
{
    public function getHeader(): string;
    public function getIdentifier(): string;
    public function getCellContent(): string;
    public function isSortable(): bool;
    public function sort(WP_Query $query): WP_Query;
}
