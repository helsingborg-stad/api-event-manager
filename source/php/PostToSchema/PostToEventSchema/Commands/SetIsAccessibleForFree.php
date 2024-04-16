<?php

namespace EventManager\PostToSchema\PostToEventSchema\Commands;

use Spatie\SchemaOrg\BaseType;

class SetIsAccessibleForFree implements CommandInterface
{
    private const META_KEY = 'pricesList';

    public function __construct(private BaseType $schema, private array $meta)
    {
    }

    public function execute(): void
    {
        $free = !isset($this->meta[self::META_KEY]) || empty($this->meta[self::META_KEY]);
        $this->schema->isAccessibleForFree($free);
    }
}
