<?php

namespace EventManager\Tests\TagReader;

use phpmock\mockery\PHPMockery;
use PHPUnit\Framework\TestCase;
use EventManager\TagReader\TagReader;

class TagReaderTest extends TestCase {

    /**
     * @testdox getTags method returns an array
     */
    public function testGetTagsReturnsArray() {
        $tagReader = new \EventManager\TagReader\TagReader('');
        $this->assertIsArray($tagReader->getTags());
    }

    /**
     * @testdox getTags extracts tags starting with # from the input string
     */
    public function testGetTagsExtractsTags() {
        $input = 'This is a #tag1 and #tag2';
        $tagReader = new \EventManager\TagReader\TagReader($input);
        $this->assertEquals(['tag1', 'tag2'], $tagReader->getTags());
    }

    /**
     * @testdox tags can not contain special characters
     */
    public function testGetTagsTriggersCanNotContainSpecialCharacters() {
        $input = 'This is a #t_g1 and #t-g2 #t!g3 #t*g4';
        PHPMockery::mock('EventManager\TagReader', "trigger_error")
            ->once()
            ->with('Tags with special characters found: #t_g1, #t-g2, #t!g3, #t*g4', E_USER_NOTICE);

        $tagReader = new TagReader($input);
        $this->assertEquals([], $tagReader->getTags());
    }

    /**
     * @testdox tags get sanitized to contain only lowercase letters and digits
     */
    public function testGetTagsSanitizesTags() {
        $input = 'This is a #Tag1';
        $tagReader = new TagReader($input);
        $this->assertEquals(['tag1'], $tagReader->getTags());
    }
    

    /**
     * @testdox tags can be extracted if they are not separated by spaces
     */
    public function testGetTagsExtractsTagsWithoutSpaces() {
        $input = 'This is a #tag1#tag2';
        $tagReader = new TagReader($input);
        $this->assertEquals(['tag1', 'tag2'], $tagReader->getTags());
    }

    /**
     * @testdox tags can be extracted from multiline strings
     */
    public function testGetTagsExtractsTagsFromMultilineStrings() {
        $input = "This is a #tag1\nand #tag2";
        $tagReader = new TagReader($input);
        $this->assertEquals(['tag1', 'tag2'], $tagReader->getTags());
    }
}