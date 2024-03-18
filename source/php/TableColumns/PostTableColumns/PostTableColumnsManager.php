<?php

namespace EventManager\TableColumns\PostTableColumns;

use EventManager\Helper\Hookable;
use EventManager\Services\WPService\AddAction;
use EventManager\Services\WPService\AddFilter;
use EventManager\TableColumns\TableColumnInterface;
use EventManager\TableColumns\TableColumnsManagerInterface;

class PostTableColumnsManager implements TableColumnsManagerInterface, Hookable
{
    public function __construct(
        private array $postTypes,
        private AddAction&AddFilter $wpService
    ) {
    }

    /**
     * @param TableColumnInterface[] $columns
     */
    private array $columns = [];

    public function register(TableColumnInterface $column): void
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
        }

        foreach ($this->postTypes as $postType) {
            $this->wpService->addAction("manage_{$postType}_posts_custom_column", [$this, 'populateTableCells'], 10, 1);
        }
    }

    public function addColumnsToTable(array $tableColumnsArray): array
    {
        foreach ($this->columns as $column) {
            $tableColumnsArray[$column->getName()] = $column->getHeader();
        }

        return $tableColumnsArray;
    }

    public function populateTableCells(string $currentColumnName): void
    {
        foreach ($this->columns as $column) {
            if ($currentColumnName === $column->getName()) {
                echo $column->getCellContent();
            }
        }
    }
}
