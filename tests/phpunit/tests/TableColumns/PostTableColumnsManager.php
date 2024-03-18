<?php

namespace EventManager\Tests\TableColumns\PostTableColumns;

use EventManager\Services\WPService\AddAction;
use EventManager\Services\WPService\AddFilter;
use EventManager\TableColumns\PostTableColumns\PostTableColumnsManager;
use EventManager\TableColumns\TableColumnInterface;
use Mockery;
use WP_Mock\Tools\TestCase;

class PostTableColumnsManagerTest extends TestCase
{
    /**
     * @test register() takes a TableColumnInterface and adds it to the list of columns
     */
    public function testRegister()
    {
        /** @var AddAction&AddFilter $wpService */
        $wpService = Mockery::mock(AddAction::class, AddFilter::class);
        $column    = $this->getTableColumn();

        $manager = new PostTableColumnsManager(['foo'], $wpService);
        $manager->register($column);

        $this->assertContains($column, $manager->getColumns());
    }

    /**
     * @testdox addHooks() adds hooks to the WordPress service to manage the post columns
     */
    public function testAddHooks()
    {
        /** @var AddAction&AddFilter|Mockery\Mock $wpService */
        $wpService = Mockery::mock(AddAction::class, AddFilter::class);
        $column    = $this->getTableColumn();

        $manager = new PostTableColumnsManager(['foo'], $wpService);
        $manager->register($column);

        $wpService->shouldReceive('addFilter')
            ->with('manage_foo_posts_columns', [$manager, 'addColumnsToTable'])
            ->once();

        $wpService->shouldReceive('addAction')
            ->with('manage_foo_posts_custom_column', [$manager, 'populateTableCells'], 10, 1)
            ->once();

        $manager->addHooks();

        $this->assertConditionsMet();
    }

    /**
     * @testdox addColumnsToTable() takes an array of table columns and adds the columns from the manager
     */
    public function testAddColumnsToTable()
    {
        /** @var AddAction&AddFilter $wpService */
        $wpService = Mockery::mock(AddAction::class, AddFilter::class);
        $column    = $this->getTableColumn('Foo', 'foo', 'bar');

        $manager = new PostTableColumnsManager(['foo'], $wpService);
        $manager->register($column);

        $tableColumnsArray = $manager->addColumnsToTable([]);

        $this->assertArrayHasKey('foo', $tableColumnsArray);
        $this->assertEquals('Foo', $tableColumnsArray['foo']);
    }

    /**
     * @testdox populateTableCells() takes a column name and prints the content of the column
     */
    public function testPopulateTableCells()
    {
        /** @var AddAction&AddFilter $wpService */
        $wpService = Mockery::mock(AddAction::class, AddFilter::class);
        $column    = $this->getTableColumn('Foo', 'foo', 'bar');

        $manager = new PostTableColumnsManager(['foo'], $wpService);
        $manager->register($column);

        ob_start();
        $manager->populateTableCells('foo');

        $this->assertEquals('bar', ob_get_clean());
    }


    private function getTableColumn($header = 'header', $name = 'name', $content = 'content'): TableColumnInterface
    {
        return new class ($header, $name, $content) implements TableColumnInterface {
            public function __construct(
                private string $header,
                private string $name,
                private string $content
            ) {
            }

            public function getHeader(): string
            {
                return $this->header;
            }

            public function getName(): string
            {
                return $this->name;
            }

            public function getCellContent(): string
            {
                return $this->content;
            }
        };
    }
}
