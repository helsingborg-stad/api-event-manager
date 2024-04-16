<?php

namespace EventManager\PostToSchema\PostToEventSchema\Commands;

use PHPUnit\Framework\TestCase;

class SetIsAccessibleForFreeTest extends TestCase
{
    /**
     * @testdox sets isAccessibleForFree to true if priceList is empty
     */
    public function testExecute()
    {
        $meta   = ['pricesList' => []];
        $schema = new \Spatie\SchemaOrg\Thing();

        $command = new SetIsAccessibleForFree($schema, $meta);
        $command->execute();

        $this->assertEquals(true, $schema->toArray()['isAccessibleForFree']);
    }

    /**
     * @testdox sets isAccessibleForFree to false if priceList is not empty
     */
    public function testExecuteWithPriceList()
    {
        $meta   = ['pricesList' => ['price']];
        $schema = new \Spatie\SchemaOrg\Thing();

        $command = new SetIsAccessibleForFree($schema, $meta);
        $command->execute();

        $this->assertEquals(false, $schema->toArray()['isAccessibleForFree']);
    }
}
