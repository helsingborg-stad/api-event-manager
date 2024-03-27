<?php

namespace EventManager\PostToSchema\PostToEventSchema\Commands;

use Spatie\SchemaOrg\BaseType;

class SetDates implements CommandInterface
{
    public function __construct(private BaseType $schema, private array $meta)
    {
    }

    public function execute(): void
    {
        $occasions = $this->meta['occasions'] ?? [];

        if (empty($occasions)) {
            return;
        }

        // StartDate is the earliest date and time of the occasions
        $startDate = min(array_map(function ($occasion) {
            return $this->formatDateFromDateAndTime($occasion['date'], $occasion['startTime']);
        }, $occasions));

        // EndDate is the latest date and time of the occasions
        $endDate = max(array_map(function ($occasion) {
            $date = $occasion['untilDate'] ?? $occasion['date'];
            return $this->formatDateFromDateAndTime($date, $occasion['endTime']);
        }, $occasions));

        $this->schema->startDate($startDate);
        $this->schema->endDate($endDate);
    }

    private function formatDateFromDateAndTime(?string $date, ?string $time): ?string
    {
        if (!$date || !$time) {
            return null;
        }

        $dateTime = new \DateTime("{$date} {$time}");
        return $dateTime->format('Y-m-d H:i');
    }
}
