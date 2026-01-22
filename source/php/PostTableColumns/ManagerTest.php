<?php

namespace EventManager\PostTableColumns;

use EventManager\HooksRegistrar\Hookable;
use PHPUnit\Framework\TestCase;
use WP_Query;
use WpService\Contracts\AddAction;
use WpService\Contracts\AddFilter;

class ManagerTest extends TestCase {
    /**
     * @testdox can be instantiated
     */
    public function testCanBeInstantiated(): void {
        $wpService = $this->createWpService();
        $manager = new Manager(['post'], $wpService);
        $this->assertInstanceOf(Manager::class, $manager);
    }

    /**
     * @testdox getColumns returns registered columns
     */
    public function testGetColumnsReturnsRegisteredColumns(): void {
        $wpService = $this->createWpService();
        $manager = new Manager(['post'], $wpService);

        $mockColumn = $this->createMock(ColumnInterface::class);
        $manager->register($mockColumn);

        $columns = $manager->getColumns();
        $this->assertCount(1, $columns);
        $this->assertSame($mockColumn, $columns[0]);
    }

    /**
     * @testdox addHooks registers the appropriate hooks
     */
    public function testAddHooksRegistersAppropriateHooks(): void {
        $wpService = static::createWpService();
        $manager = new Manager(['post'], $wpService);
        $manager->addHooks();
        
        $this->assertCount(2, $wpService->addedFilters);
        $this->assertCount(2, $wpService->addedActions);

        $this->assertEquals('manage_post_posts_columns', $wpService->addedFilters[0]['hookName']);
        $this->assertEquals('manage_edit-post_sortable_columns', $wpService->addedFilters[1]['hookName']);
        $this->assertEquals('manage_post_posts_custom_column', $wpService->addedActions[0]['hookName']);
        $this->assertEquals('pre_get_posts', $wpService->addedActions[1]['hookName']);
    }

    /**
     * @testdox addColumnsToTable adds registered columns to the table columns array
     */
    public function testAddColumnsToTableAddsRegisteredColumns(): void {
        $wpService = $this->createWpService();
        $manager = new Manager(['post'], $wpService);

        $mockColumn = $this->createMock(ColumnInterface::class);
        $mockColumn->method('getIdentifier')->willReturn('custom_column');
        $mockColumn->method('getHeader')->willReturn('Custom Column');

        $manager->register($mockColumn);

        $initialColumns = ['title' => 'Title', 'date' => 'Date'];
        $updatedColumns = $manager->addColumnsToTable($initialColumns);

        $this->assertArrayHasKey('custom_column', $updatedColumns);
        $this->assertEquals('Custom Column', $updatedColumns['custom_column']);
    }

    /**
     * @testdox addSortableColumns adds sortable columns to the sortable columns array
     */
    public function testAddSortableColumnsAddsSortableColumns(): void {
        $wpService = $this->createWpService();
        $manager = new Manager(['post'], $wpService);

        $sortableColumn = $this->createMock(ColumnInterface::class);
        $sortableColumn->method('getIdentifier')->willReturn('sortable_column');
        $sortableColumn->method('isSortable')->willReturn(true);

        $nonSortableColumn = $this->createMock(ColumnInterface::class);
        $nonSortableColumn->method('getIdentifier')->willReturn('non_sortable_column');
        $nonSortableColumn->method('isSortable')->willReturn(false);

        $manager->register($sortableColumn);
        $manager->register($nonSortableColumn);

        $initialSortableColumns = ['title' => 'title', 'date' => 'date'];
        $updatedSortableColumns = $manager->addSortableColumns($initialSortableColumns);

        $this->assertArrayHasKey('sortable_column', $updatedSortableColumns);
        $this->assertArrayNotHasKey('non_sortable_column', $updatedSortableColumns);
    }

    /**
     * @testdox populateTableCells outputs the correct cell content for the given column
     */
    public function testPopulateTableCellsOutputsCorrectCellContent(): void {
        $wpService = $this->createWpService();
        $manager = new Manager(['post'], $wpService);

        $mockColumn = $this->createMock(ColumnInterface::class);
        $mockColumn->method('getIdentifier')->willReturn('custom_column');
        $mockColumn->method('getCellContent')->willReturn('Cell Content');

        $manager->register($mockColumn);

        // Capture the output
        ob_start();
        $manager->populateTableCells('custom_column');
        $output = ob_get_clean();

        $this->assertEquals('Cell Content', $output);
    }

    private static function createWpService(): AddAction&AddFilter {
        return new class implements AddAction, AddFilter {
            public array $addedActions = [];
            public array $addedFilters = [];
            public function addAction(string $hookName, callable $callback, int $priority = 10, int $acceptedArgs = 1): true
            {
                $this->addedActions[] = compact('hookName', 'callback', 'priority', 'acceptedArgs');
                return true;
            }

            public function addFilter(string $hookName, callable $callback, int $priority = 10, int $acceptedArgs = 1): true
            {
                $this->addedFilters[] = compact('hookName', 'callback', 'priority', 'acceptedArgs');
                return true;
            }
        };
    }
}