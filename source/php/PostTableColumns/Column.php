<?php

namespace EventManager\PostTableColumns;

use EventManager\PostTableColumns\ColumnCellContent\ColumnCellContentInterface;
use EventManager\PostTableColumns\ColumnSorters\ColumnSortInterface;
use WP_Query;

class Column implements ColumnInterface
{
    public function __construct(
        private string $header,
        private string $identifier,
        private ColumnCellContentInterface $cellContent,
        private ?ColumnSortInterface $columnSorter = null
    ) {
    }

    public function getHeader(): string
    {
        return $this->header;
    }

    public function getIdentifier(): string
    {
        return $this->identifier;
    }

    public function isSortable(): bool
    {
        return $this->columnSorter !== null;
    }

    public function getCellContent(): string
    {
        return $this->cellContent->getCellContent($this->getIdentifier());
    }

    public function sort(WP_Query $query): WP_Query
    {
        return $this->columnSorter->sort($query);
    }
}
