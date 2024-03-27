<?php

namespace EventManager\PostToSchema\PostToEventSchema\Commands;

use Spatie\SchemaOrg\BaseType;

class SetAbout implements CommandInterface
{
    public function __construct(private BaseType $schema, private array $meta)
    {
    }

    public function execute(): void
    {
        $this->schema->about($this->meta['about'] ?: null);
    }
}
