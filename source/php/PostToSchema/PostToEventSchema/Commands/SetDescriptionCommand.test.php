<?php

namespace EventManager\PostToSchema\PostToEventSchema\Commands;

use PHPUnit\Framework\TestCase;

class SetDescriptionCommandTest extends TestCase
{
    /**
     * @testdox sets description from available post meta.
     */
    public function testExecute()
    {
        $meta   = ['description' => 'Test Description'];
        $schema = new \Spatie\SchemaOrg\Thing();

        $command = new SetDescriptionCommand($schema, $meta);
        $command->execute();

        $this->assertEquals('Test Description', $schema->toArray()['description']);
    }
}
