<?php

namespace EventManager\PostToSchema\PostToEventSchema\Commands;

use PHPUnit\Framework\TestCase;

class SetUrlTest extends TestCase
{
    /**
     * @testdox sets url from occasions
     */
    public function testExecute()
    {
        $meta   = ['occasions' => [['url' => 'https://example.com']]];
        $schema = new \Spatie\SchemaOrg\Thing();

        $command = new SetUrl($schema, $meta);
        $command->execute();

        $this->assertEquals('https://example.com', $schema->toArray()['url']);
    }
}
