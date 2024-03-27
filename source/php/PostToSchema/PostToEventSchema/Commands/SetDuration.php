<?php

namespace EventManager\PostToSchema\PostToEventSchema\Commands;

use Spatie\SchemaOrg\BaseType;

class SetDuration implements CommandInterface
{
    public function __construct(private BaseType $schema)
    {
    }

    public function execute(): void
    {
        $startDate = $this->schema->getProperty('startDate');
        $endDate   = $this->schema->getProperty('endDate');
        $duration  = null;

        if ($startDate && $endDate) {
            $startDate = new \DateTime($startDate);
            $endDate   = new \DateTime($endDate);

            $duration = $startDate->diff($endDate)->format('P%yY%mM%dDT%hH%iM%sS');
        }

        $this->schema->duration($duration);
    }
}
