<?php

namespace EventManager\Tests\PostTableColumns\ColumnSorters;

use PHPUnit\Framework\TestCase;
use EventManager\PostTableColumns\ColumnCellContent\NestedMetaStringCellContent;
use EventManager\PostTableColumns\Helpers\GetNestedArrayStringValueRecursive;
use WpService\Contracts\EscHtml;
use WpService\Contracts\GetPostMeta;
use WpService\Contracts\GetTheID;

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

    /**
     * @testdox getCellContent() returns escaped string for HTML special characters
     */
    public function testGetCellContentReturnsEscapedStringForHtmlSpecialCharacters() {
        $postId                             = 1;
        $meta                               = [1 => ['foo' => ['bar' => ['baz' => '<script>alert("xss")</script>']]]];
        $wpService                          = $this->getWpService($postId, $meta);
        $getNestedArrayStringValueRecursive = new GetNestedArrayStringValueRecursive();
        $nestedMetaStringCellContent        = new NestedMetaStringCellContent('foo.bar.baz', $wpService, $getNestedArrayStringValueRecursive);

        $cellContent = $nestedMetaStringCellContent->getCellContent();

        $this->assertEquals('&lt;script&gt;alert(&quot;xss&quot;)&lt;/script&gt;', $cellContent);
    }

    private function getWpService($postId = 1, $meta = []): GetTheID&GetPostMeta&EscHtml
    {
        return new class ($postId, $meta) implements GetTheID, GetPostMeta, EscHtml {
            public function __construct(private int $postId, private array $meta)
            {
            }

            public function getTheID(): int
            {
                return $this->postId;
            }

            public function getPostMeta($postId, $key = '', $single = false): mixed
            {
                return $this->meta[$postId][$key] ?? '';
            }

            public function escHtml(string $text): string
            {
                return htmlspecialchars($text, ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8');
            }
        };
    }
}
