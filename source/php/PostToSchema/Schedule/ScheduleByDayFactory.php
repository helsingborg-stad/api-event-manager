<?php

namespace EventManager\PostToSchema\Schedule;

class ScheduleByDayFactory implements ScheduleFactory
{
    private string $startDate;
    private string $untilDate;
    private string $startTime;
    private string $endTime;
    private string|int $interval;

    public function create(array $occasion): ?\Spatie\SchemaOrg\Schedule
    {
        $this->startDate = $occasion['date'] ?? null;
        $this->untilDate = $occasion['untilDate'] ?? null;
        $this->startTime = $occasion['startTime'] ?? null;
        $this->endTime   = $occasion['endTime'] ?? null;
        $this->interval  = $occasion['daysInterval'] ?? 1;

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
