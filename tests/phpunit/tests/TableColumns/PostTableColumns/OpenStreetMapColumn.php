<?php

namespace EventManager\Tests\TableColumns\PostTableColumns;

use EventManager\Services\WPService\GetPostMeta;
use EventManager\Services\WPService\GetTheId;
use EventManager\TableColumns\PostTableColumns\OpenStreetMapTableColumn;
use PHPUnit\Framework\TestCase;

class OpenStreetMapColumnTest extends TestCase
{
    /**
     * @testdox getName() returns the meta key
     */
    public function testGetName()
    {
        $wpService = $this->getWpService();
        $column    = new OpenStreetMapTableColumn('Header', 'meta_key', $wpService);

        $this->assertEquals('meta_key', $column->getName());
    }

    /**
     * @testdox getHeader() returns the header
     */
    public function testGetHeader()
    {
        $wpService = $this->getWpService();
        $column    = new OpenStreetMapTableColumn('Header', 'meta_key', $wpService);

        $this->assertEquals('Header', $column->getHeader());
    }

    /**
     * @testdox getCellContent() returns the address from the meta value, assuming it is an array with an address key
     */
    public function testGetCellContent()
    {
        $wpService = $this->getWpService();
        $column    = new OpenStreetMapTableColumn('Header', 'valid_meta_key', $wpService);

        $this->assertEquals('123 Main St', $column->getCellContent());
    }

    /**
     * @testdox getCellContent() returns empty string if the meta value is not valid
     */
    public function testGetCellContentReturnsEmptyStringIfMetaValueIsNotArray()
    {
        $wpService = $this->getWpService();
        $column    = new OpenStreetMapTableColumn('Header', 'invalid_meta_key', $wpService);

        $this->assertEquals('', $column->getCellContent());
    }

    private function getWpService(): GetPostMeta|GetTheId
    {
        return new class implements GetPostMeta, GetTheId {
            public function getTheId(): int
            {
                return 1;
            }

            public function getPostMeta($postId, $key = '', $single = false): mixed
            {
                $metaData = ['1' => ['valid_meta_key' => ['address' => '123 Main St']]];
                return $metaData[$postId][$key] ?? null;
            }
        };
    }
}
