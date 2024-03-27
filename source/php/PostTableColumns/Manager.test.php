<?php

namespace EventManager\PostTableColumns;

use EventManager\Services\WPService\AddAction;
use EventManager\Services\WPService\AddFilter;
use Mockery;
use PHPUnit\Framework\TestCase;
use WP_Query;

class ManagerTest extends TestCase
{
    use \Mockery\Adapter\Phpunit\MockeryPHPUnitIntegration;

    /**
     * @testdox register() adds a column to the columns array
     */
    public function testRegisterAddsColumnToColumnsArray(): void
    {
        /** @var AddAction&AddFilter $wpService */
        $wpService = Mockery::mock(AddAction::class, AddFilter::class);
        $column    = $this->createMock(ColumnInterface::class);
        $manager   = new Manager([], $wpService);

        $manager->register($column);

        $this->assertContains($column, $manager->getColumns());
    }

    /**
     * @testdox addHooks() adds hooks for each post type
     */
    public function testAddHooksAddsHooksForEachPostType(): void
    {
        $wpService = Mockery::mock(AddAction::class, AddFilter::class);
        $postTypes = ['test_post_type'];
        $manager   = new Manager($postTypes, $wpService);

        $wpService
            ->shouldReceive('addFilter')
            ->with("manage_test_post_type_posts_columns", Mockery::any())
            ->once();
        $wpService
            ->shouldReceive('addFilter')
            ->with("manage_edit-test_post_type_sortable_columns", Mockery::any())
            ->once();
        $wpService
            ->shouldReceive('addAction')
            ->with("manage_test_post_type_posts_custom_column", Mockery::any())
            ->once();

        // Allow additional calls to addAction that are not interesting for this test.
        $wpService->allows('addAction');

        $manager->addHooks();
    }

    /**
     * @testdox addColumnsToTable() adds columns to the table columns array
     */
    public function testAddColumnsToTableAddsColumnsToTableColumnsArray(): void
    {
        $wpService = Mockery::mock(AddAction::class, AddFilter::class);
        $column    = $this->createMock(ColumnInterface::class);
        $manager   = new Manager([], $wpService);

        $column ->expects($this->once()) ->method('getIdentifier') ->willReturn('test_column');
        $column ->expects($this->once()) ->method('getHeader') ->willReturn('Test Column');

        $manager->register($column);
        $tableColumnsArray = $manager->addColumnsToTable([]);

        $this->assertArrayHasKey('test_column', $tableColumnsArray);
        $this->assertSame('Test Column', $tableColumnsArray['test_column']);
    }

    /**
     * @testdox addSortableColumns() adds sortable columns to the sortable columns array
     */
    public function testAddSortableColumnsAddsSortableColumnsToSortableColumnsArray(): void
    {
        $wpService = Mockery::mock(AddAction::class, AddFilter::class);
        $column    = $this->createMock(ColumnInterface::class);
        $manager   = new Manager([], $wpService);

        $column->expects($this->exactly(2))->method('getIdentifier')->willReturn('test_column');
        $column->expects($this->once())->method('isSortable')->willReturn(true);

        $manager->register($column);
        $sortableColumnsArray = $manager->addSortableColumns([]);

        $this->assertArrayHasKey('test_column', $sortableColumnsArray);
        $this->assertSame('test_column', $sortableColumnsArray['test_column']);
    }

    /**
     * @testdox populateTableCells() calls populateTableCells() on the correct column
     */
    public function testPopulateTableCellsCallsPopulateTableCellsOnCorrectColumn(): void
    {
        $wpService = Mockery::mock(AddAction::class, AddFilter::class);
        $column    = $this->createMock(ColumnInterface::class);
        $manager   = new Manager([], $wpService);

        $column->expects($this->once())->method('getIdentifier')->willReturn('test_column');
        $column->expects($this->once())->method('getCellContent')->willReturn('Test Content');

        $manager->register($column);

        ob_start();
        $manager->populateTableCells('test_column');

        $this->assertEquals('Test Content', ob_get_clean());
    }

    /**
     * @testdox sort() calls sort() on the correct column
     */
    public function testSortCallsSortOnCorrectColumn(): void
    {
        $wpService = Mockery::mock(AddAction::class, AddFilter::class);
        $wpQuery   = Mockery::mock(WP_Query::class);
        $column    = $this->createMock(ColumnInterface::class);
        $manager   = new Manager([], $wpService);

        $wpQuery->allows('is_main_query')->andReturn(true);
        $wpQuery->allows('is_admin')->andReturn(true);
        $wpQuery->query_vars = ['orderby' => 'test_column'];
        $column->expects($this->once())->method('getIdentifier')->willReturn('test_column');
        $column->expects($this->once())->method('sort');

        $manager->register($column);
        $manager->sort($wpQuery);
    }
}
