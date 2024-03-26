<?php

namespace EventManager\PostToSchema\Mappers;

use PHPUnit\Framework\TestCase;

class StringToEventSchemaMapperTest extends TestCase
{
    /**
     * @testdox Mapping a schema that implements Event returns the correct schema type
     */
    public function testMapEventSchemaTypeReturnsCorrectSchemaType()
    {
        $mapper = new StringToEventSchemaMapper();
        $schema = $mapper->map('BusinessEvent');
        $this->assertInstanceOf(\Spatie\SchemaOrg\Contracts\EventContract::class, $schema);
    }

    /**
     * @testdox Mapping a schema that does not implement Event returns null
     */
    public function testMapNonEventSchemaTypeReturnsNull()
    {
        $mapper = new StringToEventSchemaMapper();

        $schema = $mapper->map('Airport');
        $this->assertNull($schema);
    }
}
