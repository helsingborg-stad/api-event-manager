<?php

namespace EventManager\Tests\TagReader;

use phpmock\mockery\PHPMockery;
use PHPUnit\Framework\TestCase;
use EventManager\TagReader\TagReader;

class TagReaderTest extends TestCase
{
    /**
     * @testdox getTags method returns an array
     */
    public function testGetTagsReturnsArray()
    {
        $tagReader = new \EventManager\TagReader\TagReader();
        $this->assertIsArray($tagReader->getTags(''));
    }

    /**
     * @testdox getTags extracts tags starting with # from the input string
     */
    public function testGetTagsExtractsTags()
    {
        $input     = 'This is a #tag1 and #tag2';
        $tagReader = new \EventManager\TagReader\TagReader();
        $this->assertEquals(['tag1', 'tag2'], $tagReader->getTags($input));
    }

    /**
     * @testdox tags can contain å ä ö
     */
    public function testGetTagsTriggersCanContainAaOo()
    {
        $input = 'This is a #tåg1 and #täg2 #tög3';

        $tagReader = new TagReader();

        $this->assertEquals(['tåg1', 'täg2', 'tög3'], $tagReader->getTags($input));
    }

    /**
     * @testdox tags can not contain special characters
     */
    public function testGetTagsTriggersCanNotContainSpecialCharacters()
    {
        $input = 'This is a #t_g1 and #t-g2 #t!g3 #t*g4';

        $tagReader = new TagReader();

        $this->assertEquals([], $tagReader->getTags($input));
    }

    /**
     * @testdox tags get sanitized to contain only lowercase letters and digits
     */
    public function testGetTagsSanitizesTags()
    {
        $input     = 'This is a #Tag1';
        $tagReader = new TagReader();
        $this->assertEquals(['tag1'], $tagReader->getTags($input));
    }


    /**
     * @testdox tags can be extracted if they are not separated by spaces
     */
    public function testGetTagsExtractsTagsWithoutSpaces()
    {
        $input     = 'This is a #tag1#tag2';
        $tagReader = new TagReader();
        $this->assertEquals(['tag1', 'tag2'], $tagReader->getTags($input));
    }

    /**
     * @testdox tags can be extracted from multiline strings
     */
    public function testGetTagsExtractsTagsFromMultilineStrings()
    {
        $input     = "This is a #tag1\nand #tag2";
        $tagReader = new TagReader();
        $this->assertEquals(['tag1', 'tag2'], $tagReader->getTags($input));
    }
}
