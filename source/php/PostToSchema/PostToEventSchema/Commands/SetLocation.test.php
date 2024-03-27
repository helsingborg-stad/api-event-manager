<?php

namespace EventManager\PostToSchema\PostToEventSchema\Commands;

use EventManager\PostToSchema\PostToEventSchema\Commands\Helpers\CommandHelpers;
use PHPUnit\Framework\TestCase;

class SetLocationTest extends TestCase
{
    /**
     * @testdox sets location from available post meta.
     */
    public function testExecute()
    {
        $meta    = ['location' => ['address' => '123 Main St.', 'lat' => 123.456, 'lng' => 456.789]];
        $schema  = new \Spatie\SchemaOrg\Thing();
        $helpers = new CommandHelpers();

        $command = new SetLocation($schema, $meta, $helpers);
        $command->execute();

        $this->assertEquals('123 Main St.', $schema->toArray()['location']['address']);
        $this->assertEquals(123.456, $schema->toArray()['location']['latitude']);
        $this->assertEquals(456.789, $schema->toArray()['location']['longitude']);
    }
}
