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

        if (empty($occasions) || count($occasions) !== 1) {
            return;
        }

        $repeat    = $occasions[0]['repeat'] ?: null;
        $date      = $occasions[0]['date'] ?: null;
        $startTime = $occasions[0]['startTime'] ?: null;
        $endTime   = $occasions[0]['endTime'] ?: null;

        if ($repeat !== 'no') {
            return;
        }

        if ($this->endTimeIsEarlierThanStartTime($startTime, $endTime)) {
            $endTime = null;
        }

        $this->schema->startDate($this->formatDateFromDateAndTime($date, $startTime));
        $this->schema->endDate($this->formatDateFromDateAndTime($date, $endTime));
    }

    private function endTimeIsEarlierThanStartTime(?string $startTime, ?string $endTime): bool
    {
        if (!$startTime || !$endTime) {
            return false;
        }

        $startTimeUnix = strtotime($startTime);
        $endTimeUnix   = strtotime($endTime);

        return $endTimeUnix < $startTimeUnix;
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
