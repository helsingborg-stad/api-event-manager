<?php

namespace EventManager\PostToSchema\PostToEventSchema\Commands;

use PHPUnit\Framework\TestCase;

class SetAboutCommandTest extends TestCase
{
    /**
     * @testdox sets about from available post meta.
     */
    public function testExecute()
    {
        $meta   = ['about' => 'Test About'];
        $schema = new \Spatie\SchemaOrg\Thing();

        $command = new SetAbout($schema, $meta);
        $command->execute();

        $this->assertEquals('Test About', $schema->toArray()['about']);
    }
}
