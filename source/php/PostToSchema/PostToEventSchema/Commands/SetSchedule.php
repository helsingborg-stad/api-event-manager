<?php

namespace EventManager\PostToSchema\PostToEventSchema\Commands;

use EventManager\PostToSchema\Schedule\ScheduleByDayFactory;
use EventManager\PostToSchema\Schedule\ScheduleByMonthFactory;
use EventManager\PostToSchema\Schedule\ScheduleByWeekFactory;
use Spatie\SchemaOrg\BaseType;

class SetSchedule implements CommandInterface
{
    public function __construct(private BaseType $event, private array $meta)
    {
    }

    public function execute(): void
    {
        $schedules = [];
        $occasions = $this->meta['occasions'] ?: null;

        if (!is_array($occasions) || sizeof($occasions) < 1) {
            return;
        }

        $schedules = array_map(function ($occasion) {
            $repeat    = $occasion['repeat'];
            $startDate = $occasion['date'];
            $untilDate = $occasion['untilDate'];
            $startTime = $occasion['startTime'];
            $endTime   = $occasion['endTime'];

            switch ($repeat) {
                case 'byDay':
                    $daysInterval    = $occasion['daysInterval'] ?: 1;
                    $scheduleFactory = new ScheduleByDayFactory(
                        $startDate,
                        $untilDate,
                        $startTime,
                        $endTime,
                        $daysInterval
                    );
                    return $scheduleFactory->create();
                case 'byWeek':
                    $daysInterval    = $occasion['weeksInterval'] ?: 1;
                    $weekDays        = $occasion['weekDays'] ?: [];
                    $scheduleFactory = new ScheduleByWeekFactory(
                        $startDate,
                        $untilDate,
                        $startTime,
                        $endTime,
                        $daysInterval,
                        $weekDays
                    );
                    return $scheduleFactory->create();
                case 'byMonth':
                    $daysInterval    = $occasion['monthsInterval'] ?: 1;
                    $monthDay        = $occasion['monthDay'] ?: null;
                    $monthDayNumber  = $occasion['monthDayNumber'] ?: null;
                    $monthDayLiteral = $occasion['monthDayLiteral'] ?: null;
                    $scheduleFactory = new ScheduleByMonthFactory(
                        $startDate,
                        $untilDate,
                        $startTime,
                        $endTime,
                        $daysInterval,
                        $monthDay,
                        $monthDayNumber,
                        $monthDayLiteral
                    );
                    return $scheduleFactory->create();
                dafault:
                    return null;
            }
        }, $occasions);

        $schedules = array_filter($schedules); // Remove null values

        $this->event->eventSchedule($schedules ?: null);
    }
}
