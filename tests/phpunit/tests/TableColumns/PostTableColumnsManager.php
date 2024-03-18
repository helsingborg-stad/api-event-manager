<?php

namespace EventManager\Tests\TableColumns\PostTableColumns;

use EventManager\Services\WPService\AddAction;
use EventManager\Services\WPService\AddFilter;
use EventManager\TableColumns\PostTableColumns\PostTableColumnsManager;
use EventManager\TableColumns\TableColumnInterface;
use Mockery;
use PHPUnit\Framework\TestCase;

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

        $manager = new PostTableColumnsManager($wpService);
        $manager->register($column);

        $this->assertContains($column, $manager->getColumns());
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
