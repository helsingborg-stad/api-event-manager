<?php

namespace EventManager\Tests\PostTableColumns\ColumnCellContent;

use EventManager\PostTableColumns\ColumnCellContent\MetaStringCellContent;
use WpService\Contracts\GetPostMeta;
use WpService\Contracts\GetTheID;
use PHPUnit\Framework\TestCase;
use WpService\Contracts\EscHtml;

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

    /**
     * @testdox getCellContent() returns value with HTML escaped
     */
    public function testGetCellContentEscapesHtml() {
        $wpService             = $this->getWpService(1, [1 => ['foo' => '<script>alert("xss")</script>']]);
        $metaStringCellContent = new MetaStringCellContent('foo', $wpService);

        $cellContent = $metaStringCellContent->getCellContent();

        $this->assertEquals('&lt;script&gt;alert(&quot;xss&quot;)&lt;/script&gt;', $cellContent);
    }

    private function getWpService($postId = 1, $meta = []): GetPostMeta|GetTheID|EscHtml
    {
        return new class ($postId, $meta) implements GetPostMeta, GetTheID, EscHtml {
            public function __construct(private int $postId, private array $meta)
            {
            }

            public function getTheID(): int|false
            {
                return $this->postId;
            }

            public function getPostMeta($postId, $key = '', $single = false): mixed
            {
                return $this->meta[$postId][$key] ?? '';
            }

            public function escHtml(string $text): string
            {
                $text = htmlspecialchars($text, ENT_QUOTES, 'UTF-8');
                return $text;
            }
        };
    }
}
