<?php

namespace EventManager\PostToSchema\PostToEventSchema\Commands;

use PHPUnit\Framework\TestCase;

class SetIsAccessibleForFreeCommandTest extends TestCase
{
    /**
     * @testdox sets isAccessibleForFree from available post meta.
     */
    public function testExecute()
    {
        $meta   = ['isAccessibleForFree' => true];
        $schema = new \Spatie\SchemaOrg\Thing();

        $command = new SetIsAccessibleForFreeCommand($schema, $meta);
        $command->execute();

        $this->assertEquals(true, $schema->toArray()['isAccessibleForFree']);
    }
}
