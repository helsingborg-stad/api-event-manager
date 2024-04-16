<?php

namespace EventManager\PostToSchema\PostToEventSchema\Commands;

use PHPUnit\Framework\TestCase;

class SetOffersTest extends TestCase
{
    /**
     * @testdox does not set offers if priceList is empty
     */
    public function testExecuteEmptyDoesNotSetOffers()
    {
        $meta   = ['pricesList' => []];
        $schema = new \Spatie\SchemaOrg\Thing();

        $command = new SetOffers($schema, $meta);
        $command->execute();

        $this->assertArrayNotHasKey('offers', $schema->toArray());
    }

    /**
     * @testdox sets does not set offers if priceList is empty
     */
    public function testExecuteSetsOffers()
    {
        $meta   = [ 'pricesList' => [ [ 'priceLabel' => 'Standard Price', 'price' => '100' ] ] ];
        $schema = new \Spatie\SchemaOrg\Thing();

        $command = new SetOffers($schema, $meta);
        $command->execute();

        $this->assertEquals('Offer', $schema->toArray()['offers'][0]['@type']);
        $this->assertEquals('SEK', $schema->toArray()['offers'][0]['priceCurrency']);
        $this->assertEquals('Standard Price', $schema->toArray()['offers'][0]['name']);
        $this->assertEquals('100', $schema->toArray()['offers'][0]['price']);
    }
}
