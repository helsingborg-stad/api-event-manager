<?php

namespace EventManager\Tests\PostTableColumns\ColumnCellContent;

use EventManager\PostTableColumns\ColumnCellContent\TermNameCellContent;
use WpService\Contracts\GetEditTermLink;
use WpService\Contracts\GetPostTerms;
use WpService\Contracts\GetTheId;
use Mockery;
use PHPUnit\Framework\TestCase;
use WP_Error;
use WP_Term;

class TermNameCellContentTest extends TestCase
{
    /**
     * @testdox getCellContent returns a link to the edit term page
     */
    public function testGetCellContentReturnsTheValueAsAString()
    {
        $term           = Mockery::mock(WP_Term::class);
        $term->term_id  = 321;
        $term->name     = 'foo';
        $term->taxonomy = 'testTaxonomy';

        $wpService           = $this->getWpService(123, ['testTaxonomy' => [123 => [$term]]]);
        $termNameCellContent = new TermNameCellContent('testTaxonomy', $wpService);

        $this->assertEquals('<a href="foo?edit">foo</a>', $termNameCellContent->getCellContent());
    }

    /**
     * @testdox getCellContent returns empty string if no terms are found
     */
    public function testGetCellContentReturnsEmptyStringIfNoTermsAreFound()
    {
        $wpService           = $this->getWpService(123, ['testTaxonomy' => [123 => []]]);
        $termNameCellContent = new TermNameCellContent('testTaxonomy', $wpService);

        $this->assertEquals('', $termNameCellContent->getCellContent());
    }

    /**
     * @testdox getCellContent returns empty string if WP_Error is returned from WPService
     */
    public function testGetCellContentReturnsEmptyStringIfWpErrorIsReturnedFromWpService()
    {
        $wpError             = Mockery::mock(WP_Error::class);
        $wpService           = $this->getWpService(123, ['testTaxonomy' => [123 => $wpError]]);
        $termNameCellContent = new TermNameCellContent('testTaxonomy', $wpService);

        $this->assertEquals('', $termNameCellContent->getCellContent());
    }

    private function getWpService($postId = 1, $terms = []): GetTheId&GetPostTerms&GetEditTermLink
    {
        return new class ($postId, $terms) implements GetTheId, GetPostTerms, GetEditTermLink {
            public function __construct(private int $postId, private array $terms)
            {
            }

            public function getTheId(): int|false
            {
                return $this->postId;
            }

            public function getPostTerms(int $post_id, string|array $taxonomy = 'post_tag', array $args = array()): array|WP_Error
            {
                return $this->terms[$taxonomy][$post_id] ?? [];
            }

            public function getEditTermLink(int|WP_Term $term, string $taxonomy = '', string $objectType = ''): ?string
            {
                return $term->name . '?edit';
            }
        };
    }
}
