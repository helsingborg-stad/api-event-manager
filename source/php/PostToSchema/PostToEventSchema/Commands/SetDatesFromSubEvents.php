<?php

namespace EventManager\PostToSchema\PostToEventSchema\Commands;

use Spatie\SchemaOrg\BaseType;

class SetDatesFromSubEvents implements CommandInterface
{
    public function __construct(private BaseType $schema)
    {
    }

    public function execute(): void
    {
        $subEvents = $this->schema['subEvents'];

        if (empty($subEvents)) {
            return;
        }

        $startDate = min(array_map(function ($subEvent) {
            return $subEvent['startDate'];
        }, $subEvents));

        // Set $this->schema endDate to the latest endDate of the subEvents
        $endDate = max(array_map(function ($subEvent) {
            return $subEvent['endDate'];
        }, $subEvents));

        $this->schema->startDate($startDate);
        $this->schema->endDate($endDate);
    }
}
