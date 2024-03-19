<?php

namespace EventManager\Tests\PostTableColumns\ColumnCellContent;

use EventManager\PostTableColumns\ColumnCellContent\MetaStringCellContent;
use EventManager\Services\WPService\GetPostMeta;
use EventManager\Services\WPService\GetTheId;
use PHPUnit\Framework\TestCase;

class MetaStringCellContentTest extends TestCase
{
    /**
     * @testdox getCellContent() returns value if meta value is a string
     */
    public function testGetCellContentReturnsTheValueAsAString()
    {
        $wpService             = $this->getWpService(1, [1 => ['foo' => 'bar']]);
        $metaStringCellContent = new MetaStringCellContent('foo', $wpService);

        $cellContent = $metaStringCellContent->getCellContent();

        $this->assertEquals('bar', $cellContent);
    }

    /**
     * @testdox getCellContent() returns number as a string if meta value is a number
     */
    public function testGetCellContentReturnsNumberAsString()
    {
        $wpService             = $this->getWpService(1, [1 => ['foo' => 123]]);
        $metaStringCellContent = new MetaStringCellContent('foo', $wpService);

        $cellContent = $metaStringCellContent->getCellContent();

        $this->assertEquals('123', $cellContent);
    }

    /**
     * @testdox getCellContent() returns empty string if meta value is not a string or a number
     */
    public function testGetCellContentReturnsEmptyString()
    {
        $wpService             = $this->getWpService(1, [1 => ['foo' => []]]);
        $metaStringCellContent = new MetaStringCellContent('foo', $wpService);

        $cellContent = $metaStringCellContent->getCellContent();

        $this->assertEquals('', $cellContent);
    }

    private function getWpService($postId = 1, $meta = []): GetPostMeta|GetTheId
    {
        return new class ($postId, $meta) implements GetPostMeta, GetTheId {
            public function __construct(private int $postId, private array $meta)
            {
            }

            public function getTheId(): int|false
            {
                return $this->postId;
            }

            public function getPostMeta($postId, $key = '', $single = false): mixed
            {
                return $this->meta[$postId][$key] ?? '';
            }
        };
    }
}
