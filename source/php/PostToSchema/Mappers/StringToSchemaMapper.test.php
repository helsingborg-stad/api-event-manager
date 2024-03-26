<?php

namespace EventManager\PostToSchema\Mappers;

use PHPUnit\Framework\TestCase;

class StringToSchemaTypeMapperTest extends TestCase
{
    /**
     * @testdox Test that mapping an existing schema type returns the correct schema type
     */
    public function testMapExistingSchemaTypeReturnsCorrectSchemaType()
    {
        $mapper = new StringToSchemaMapper();
        $schema = $mapper->map('Event');
        $this->assertInstanceOf(\Spatie\SchemaOrg\Event::class, $schema);
    }

    /**
     * @testdox Test that mapping a non-existing schema type returns null
     */
    public function testMapNonExistingSchemaTypeReturnsNull()
    {
        $mapper = new StringToSchemaMapper();
        $schema = $mapper->map('NonExistingSchemaType');
        $this->assertNull($schema);
    }
}
