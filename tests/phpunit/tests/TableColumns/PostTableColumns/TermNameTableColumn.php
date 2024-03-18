<?php

namespace EventManager\Tests\TableColumns\PostTableColumns;

use EventManager\Services\WPService\GetPostTerms;
use EventManager\Services\WPService\GetTheId;
use EventManager\Services\WPService\IsWPError;
use EventManager\TableColumns\PostTableColumns\TermNameTableColumn;
use Mockery;
use PHPUnit\Framework\TestCase;
use WP_Error;
use WP_Term;

class TermNameTableColumnTest extends TestCase
{
    /**
     * @testdox getName() returns the taxonomy
     */
    public function testGetName()
    {
        $wpService           = $this->getWpService();
        $termNameTableColumn = new TermNameTableColumn('Header', 'taxonomy_name', $wpService);

        $this->assertEquals('taxonomy_name', $termNameTableColumn->getName());
    }

    /**
     * @testdox getHeader() returns the header
     */
    public function testGetHeader()
    {
        $wpService           = $this->getWpService();
        $termNameTableColumn = new TermNameTableColumn('Header', 'taxonomy_name', $wpService);

        $this->assertEquals('Header', $termNameTableColumn->getHeader());
    }

    /**
     * @testdox getCellContent() returns the term name
     */
    public function testGetCellContent()
    {
        $wpService           = $this->getWpService();
        $termNameTableColumn = new TermNameTableColumn('Header', 'valid_taxonomy', $wpService);

        $this->assertEquals('Term Name', $termNameTableColumn->getCellContent());
    }

    private function getWpService()
    {
        return new class implements GetTheId, GetPostTerms, IsWPError {
            public function getTheId(): int|false
            {
                return 1;
            }

            public function getPostTerms(
                int $post_id,
                string|array $taxonomy = 'post_tag',
                array $args = array()
            ): array|WP_Error {
                $term       = Mockery::mock(WP_Term::class);
                $term->name = 'Term Name';
                $terms      = ['1' => ['valid_taxonomy' => [$term]]];
                return $terms[$post_id][$taxonomy] ?? [];
            }

            public function isWPError(mixed $thing): bool
            {
                return false;
            }
        };
    }
}
