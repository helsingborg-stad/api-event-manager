<?php

namespace EventManager\PostToSchema\PostToEventSchema\Commands;

use Spatie\SchemaOrg\BaseType;

class SetIsAccessibleForFree implements CommandInterface
{
    public function __construct(private BaseType $schema, private array $meta)
    {
    }

    public function execute(): void
    {
        $this->schema->isAccessibleForFree((bool) $this->meta['isAccessibleForFree'] ?: null);
    }
}
