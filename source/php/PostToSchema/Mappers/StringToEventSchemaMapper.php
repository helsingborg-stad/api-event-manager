<?php

namespace EventManager\PostToSchema\Mappers;

use Spatie\SchemaOrg\BaseType;

class StringToEventSchemaMapper implements IStringToSchemaMapper
{
    public function map(string $schemaType): ?BaseType
    {
        $schemaType = '\\Spatie\\SchemaOrg\\' . $schemaType;

        if (class_exists($schemaType) && $this->classImplementsEventContract($schemaType)) {
            return new $schemaType();
        }

        return null;
    }

    private function classImplementsEventContract(string $schemaType): bool
    {
        return in_array(\Spatie\SchemaOrg\Contracts\EventContract::class, class_implements($schemaType));
    }
}
