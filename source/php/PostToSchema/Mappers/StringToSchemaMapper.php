<?php

namespace EventManager\PostToSchema\Mappers;

use Spatie\SchemaOrg\BaseType;

class StringToSchemaMapper implements IStringToSchemaMapper
{
    public function map(string $schemaType): ?BaseType
    {
        $schemaType = '\\Spatie\\SchemaOrg\\' . $schemaType;

        if (class_exists($schemaType)) {
            return new $schemaType();
        }

        return null;
    }
}
