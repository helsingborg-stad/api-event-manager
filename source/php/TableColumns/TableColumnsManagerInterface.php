<?php

namespace EventManager\TableColumns;

interface TableColumnsManagerInterface
{
    public function register(TableColumnInterface $column): void;

    /**
     * @return TableColumnInterface[]
     */
    public function getColumns(): array;
}
