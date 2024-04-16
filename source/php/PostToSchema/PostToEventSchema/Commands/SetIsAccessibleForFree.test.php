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
     * @testdox sets isAccessibleForFree to true if no price is more than 0
     */
    public function testExecuteWithZeroPrice()
    {
        $meta   = ['pricesList' => [ ['priceLabel' => 'Standard Price', 'price' => '0'] ]];
        $schema = new \Spatie\SchemaOrg\Thing();

        $command = new SetIsAccessibleForFree($schema, $meta);
        $command->execute();

        $this->assertTrue($schema->toArray()['isAccessibleForFree']);
    }

    /**
     * @testdox sets isAccessibleForFree to false if priceList contains price greater than 0
     */
    public function testExecuteWithPriceList()
    {
        $meta   = ['pricesList' => [ ['priceLabel' => 'Standard Price', 'price' => '100'] ]];
        $schema = new \Spatie\SchemaOrg\Thing();

        $command = new SetIsAccessibleForFree($schema, $meta);
        $command->execute();

        $this->assertFalse($schema->toArray()['isAccessibleForFree']);
    }
}
