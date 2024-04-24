<?php

namespace EventManager\Tests\Taxonomies;

use WpService\WpService;
use PHPUnit\Framework\MockObject\MockObject;
use WP_Mock\Tools\TestCase;

class Keywords extends TestCase
{
    private function getMockedWpService(): WPService|MockObject
    {
        return $this
            ->getMockBuilder(WpService::class)
            ->disableOriginalConstructor()
            ->getMock();
    }

    private function getSUT()
    {
        $wpServiceMock = $this->getMockedWpService();
        return new \EventManager\Taxonomies\Keyword($wpServiceMock);
    }

    public function testTaxonomyKeyIsKeyword()
    {
        $keywordTaxonomy = $this->getSUT();
        $this->assertEquals('keyword', $keywordTaxonomy->getName());
    }

    public function testTaxonomyObjectTypeIsEvent()
    {
        $keywordTaxonomy = $this->getSUT();
        $this->assertEquals('event', $keywordTaxonomy->getObjectType());
    }

    public function testTaxonomyIsNotHierarchical()
    {
        $keywordTaxonomy = $this->getSUT();
        $this->assertFalse($keywordTaxonomy->getArgs()['hierarchical']);
    }
}
