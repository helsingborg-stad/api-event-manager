<?php

namespace EventManager\PostToSchema\PostToEventSchema\Commands;

use EventManager\PostToSchema\Schedule\NullScheduleFactory;
use EventManager\PostToSchema\Schedule\ScheduleByDayFactory;
use EventManager\PostToSchema\Schedule\ScheduleByMonthFactory;
use EventManager\PostToSchema\Schedule\ScheduleByWeekFactory;
use EventManager\PostToSchema\Schedule\ScheduleFactory;
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
            $factory = $this->getFactory($occasion['repeat']);
            return $factory->create($occasion);
        }, $occasions);

        $this->event->eventSchedule(array_filter($schedules) ?: null);
    }

    public function getFactory($repeat): ScheduleFactory
    {
        return [
            'byDay'   => new ScheduleByDayFactory(),
            'byWeek'  => new ScheduleByWeekFactory(),
            'byMonth' => new ScheduleByMonthFactory(),
        ][$repeat] ?? new NullScheduleFactory();
    }
}
