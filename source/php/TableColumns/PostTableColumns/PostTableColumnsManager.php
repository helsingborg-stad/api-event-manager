<?php

namespace EventManager\TableColumns\PostTableColumns;

use EventManager\Helper\Hookable;
use EventManager\Services\WPService\AddAction;
use EventManager\Services\WPService\AddFilter;
use EventManager\TableColumns\TableColumnInterface;
use EventManager\TableColumns\TableColumnsManagerInterface;

class PostTableColumnsManager implements TableColumnsManagerInterface, Hookable
{
    public function __construct(private AddAction&AddFilter $wpService)
    {
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
        $this->addColumnsToTable();
        $this->populateTableCells();
    }

    public function addColumnsToTable(): void
    {
        $this->wpService->addFilter('manage_event_posts_columns', function (array $tableColumnsArray) {

            foreach ($this->columns as $column) {
                $tableColumnsArray[$column->getName()] = $column->getHeader();
            }

            return $tableColumnsArray;
        });
    }

    public function populateTableCells(): void
    {
        $this->wpService->addAction('manage_event_posts_custom_column', function (string $currentColumnName, int $postId) {
            foreach ($this->columns as $column) {
                if ($currentColumnName === $column->getName()) {
                    echo $column->getCellContent();
                }
            }
        }, 10, 2);
    }
}
