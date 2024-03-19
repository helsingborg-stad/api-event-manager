<?php

namespace EventManager\PostTableColumns;

interface ManagerInterface
{
    public function register(ColumnInterface $column): void;

    /**
     * @return ColumnInterface[]
     */
    public function getColumns(): array;
}
