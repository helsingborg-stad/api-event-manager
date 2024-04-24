<?php

namespace EventManager\PostToSchema\PostToEventSchema\Commands;

use AcfService\Contracts\GetFields;
use Spatie\SchemaOrg\BaseType;

class SetTypicalAgeRange implements CommandInterface
{
    public function __construct(private BaseType $schema, private GetFields $acfService)
    {
    }

    public function execute(): void
    {
        $audience = $this->schema->getProperty('audience');
        $range    = null;

        if (!$audience || !$audience->getProperty('identifier')) {
            return;
        }

        $termId     = $audience->getProperty('identifier');
        $termFields = $this->acfService->getFields("audience_{$termId}") ?: [];
        $rangeStart = $termFields['typicalAgeRangeStart'] ?: null;
        $rangeEnd   = $termFields['typicalAgeRangeEnd'] ?: null;

        if ($rangeStart && $rangeEnd) {
            $range = "{$rangeStart}-{$rangeEnd}";
        } elseif ($rangeStart) {
            $range = "{$rangeStart}-";
        }

        $this->schema->typicalAgeRange($range);
    }
}
