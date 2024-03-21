<?php

namespace EventManager\PostToSchema\Mappers;

use Spatie\SchemaOrg\BaseType;

interface IStringToSchemaMapper
{
    public function map(string $schemaType): ?BaseType;
}
