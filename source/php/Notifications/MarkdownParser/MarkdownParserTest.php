<?php

namespace EventManager\Notifications\MarkdownParser;

use PHPUnit\Framework\TestCase;

class MarkdownParserTest extends TestCase
{
    /**
     * @testdox parses markdown to HTML
     */
    public function testParse(): void
    {
        $markdown     = "# Heading\n\nThis is a **bold** text and this is an *italic* text.";
        $expectedHtml = "<h1>Heading</h1>\n<p>This is a <strong>bold</strong> text and this is an <em>italic</em> text.</p>";

        $parser     = new MarkdownParser();
        $actualHtml = $parser->parse($markdown);

        $this->assertEquals($expectedHtml, $actualHtml);
    }
}
