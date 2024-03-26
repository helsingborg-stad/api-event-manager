<?php

namespace EventManager\PostToSchema\PostToEventSchema\Commands;

use Spatie\SchemaOrg\BaseType;

class SetAccessabilityInformation implements CommandInterface
{
    public function __construct(private BaseType $schema, private array $meta)
    {
    }

    public function execute(): void
    {
        $accessabilityInformation = $this->meta['accessabilityInformation'] ?: null;
        $about                    = $this->schema->getProperty('about');

        if ($accessabilityInformation && $about) {
            $this->schema->about("{$about}\n\n{$accessabilityInformation}");
        }
    }
}
