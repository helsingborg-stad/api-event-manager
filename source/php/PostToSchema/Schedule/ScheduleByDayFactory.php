<?php

namespace EventManager\PostToSchema\Schedule;

class ScheduleByDayFactory implements ScheduleFactory
{
    public function __construct(
        private string $startDate,
        private string $untilDate,
        private string $startTime,
        private string $endTime,
        private string|int $interval
    ) {
    }

    public function create(): ?\Spatie\SchemaOrg\Schedule
    {
        $iso8601Interval = "P{$this->interval}D";

        $schedule = new \Spatie\SchemaOrg\Schedule();
        $schedule->startDate($this->startDate);
        $schedule->startTime($this->startTime);
        $schedule->endDate($this->untilDate);
        $schedule->endTime($this->endTime);
        $schedule->repeatFrequency($iso8601Interval);
        $schedule->repeatCount($this->getRepeatCount());

        return $schedule;
    }

    private function getRepeatCount(): ?int
    {
        $startDateTime = new \DateTime($this->startDate);
        $endDateTime   = new \DateTime($this->untilDate);
        $interval      = $startDateTime->diff($endDateTime);
        $days          = $interval->days;
        $repeatCount   = ($days + 1) / (int)$this->interval;
        $repeatCount   = ceil($repeatCount);

        return $repeatCount ?: null;
    }
}
