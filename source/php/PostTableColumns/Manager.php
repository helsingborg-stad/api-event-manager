<?php

namespace EventManager\PostTableColumns;

use EventManager\Helper\Hookable;
use EventManager\Services\WPService\AddAction;
use EventManager\Services\WPService\AddFilter;
use WP_Query;

class Manager implements ManagerInterface, Hookable
{
    public function __construct(
        public array $postTypes,
        private AddAction&AddFilter $wpService
    ) {
    }

    /**
     * @param ColumnInterface[] $columns
     */
    private array $columns = [];

    public function register(ColumnInterface $column): void
    {
        $this->columns[] = $column;
    }

    public function getColumns(): array
    {
        return $this->columns;
    }

    public function addHooks(): void
    {
        foreach ($this->postTypes as $postType) {
            $this->wpService->addFilter("manage_{$postType}_posts_columns", [$this, 'addColumnsToTable']);
            $this->wpService->addFilter("manage_edit-{$postType}_sortable_columns", [$this, 'addSortableColumns']);
        }

        foreach ($this->postTypes as $postType) {
            $this->wpService->addAction("manage_{$postType}_posts_custom_column", [$this, 'populateTableCells']);
        }

        $this->wpService->addAction("pre_get_posts", [$this, 'sort']);
    }

    public function addColumnsToTable(array $tableColumnsArray): array
    {
        foreach ($this->columns as $column) {
            $tableColumnsArray[$column->getIdentifier()] = $column->getHeader();
        }

        return $tableColumnsArray;
    }

    public function addSortableColumns(array $sortableColumnsArray): array
    {
        foreach ($this->columns as $column) {
            if ($column->isSortable()) {
                $sortableColumnsArray[$column->getIdentifier()] = $column->getIdentifier();
            }
        }

        return $sortableColumnsArray;
    }

    public function populateTableCells(string $currentColumn): void
    {
        foreach ($this->columns as $column) {
            if ($currentColumn === $column->getIdentifier()) {
                echo $column->getCellContent();
            }
        }
    }

    public function sort(WP_Query &$query)
    {
        if (!$query->is_admin() || !$query->is_main_query()) {
            return;
        }

        $orderby = $query->query_vars['orderby'];

        foreach ($this->columns as $column) {
            if ($column->getIdentifier() === $orderby) {
                $query = $column->sort($query);
            }
        }
    }
}
