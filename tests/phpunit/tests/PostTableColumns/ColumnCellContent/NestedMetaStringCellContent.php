<?php

namespace EventManager\Tests\PostTableColumns\ColumnSorters;

use PHPUnit\Framework\TestCase;
use EventManager\PostTableColumns\ColumnCellContent\NestedMetaStringCellContent;
use EventManager\PostTableColumns\Helpers\GetNestedArrayStringValueRecursive;
use EventManager\Services\WPService\GetPostMeta;
use EventManager\Services\WPService\GetTheId;

/**
 * @covers \EventManager\PostTableColumns\ColumnCellContent\NestedMetaStringCellContent
 */
class NestedMetaStringCellContentTest extends TestCase
{
    /**
     * @testdox getCellContent() returns the nested value as a string
     */
    public function testGetCellContentReturnsTheNestedValueAsAString()
    {
        $postId                             = 1;
        $meta                               = [1 => ['foo' => ['bar' => ['baz' => 'booze']]]];
        $wpService                          = $this->getWpService($postId, $meta);
        $getNestedArrayStringValueRecursive = new GetNestedArrayStringValueRecursive();
        $nestedMetaStringCellContent        = new NestedMetaStringCellContent('foo.bar.baz', $wpService, $getNestedArrayStringValueRecursive);

        $cellContent = $nestedMetaStringCellContent->getCellContent();

        $this->assertEquals('booze', $cellContent);
    }

    /**
     * @testdox getCellContent() returns empty string if the nested value is not found
     */
    public function testGetCellContentReturnsEmptyStringIfTheNestedValueIsNotFound()
    {
        $wpService                          = $this->getWpService();
        $getNestedArrayStringValueRecursive = new GetNestedArrayStringValueRecursive();
        $nestedMetaStringCellContent        = new NestedMetaStringCellContent('fe.fi.fo', $wpService, $getNestedArrayStringValueRecursive);

        $cellContent = $nestedMetaStringCellContent->getCellContent();

        $this->assertEquals('', $cellContent);
    }

    private function getWpService($postId = 1, $meta = []): GetTheId&GetPostMeta
    {
        return new class ($postId, $meta) implements GetTheId, GetPostMeta {
            public function __construct(private int $postId, private array $meta)
            {
            }

            public function getTheId(): int
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
