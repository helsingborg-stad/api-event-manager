<?php

namespace EventManager\PostToSchema\PostToEventSchema\Commands;

use EventManager\PostToSchema\PostToEventSchema\Commands\Helpers\MapOpenStreetMapDataToPlace;
use Spatie\SchemaOrg\BaseType;

class SetLocation implements CommandInterface
{
    public function __construct(
        private BaseType $schema,
        private array $meta,
        private MapOpenStreetMapDataToPlace $helpers
    ) {
    }

    public function execute(): void
    {
        $location = $this->meta['location'] ?? null;

        if (!$location || !is_array($location)) {
            return;
        }

        $place = $this->helpers->mapOpenStreetMapDataToPlace($location);
        $this->schema->location($place);
    }
}
