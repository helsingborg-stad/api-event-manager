<?php

namespace EventManager\PostToSchema\Schedule;

class ScheduleByWeekFactory implements ScheduleFactory
{
    public function __construct(
        private string $startDate,
        private string $untilDate,
        private string $startTime,
        private string $endTime,
        private string|int $interval,
        private array $weekDays
    ) {
    }

    public function create(): ?\Spatie\SchemaOrg\Schedule
    {
        $iso8601Interval = "P{$this->interval}W";

        $schedule = new \Spatie\SchemaOrg\Schedule();
        $schedule->startDate($this->startDate);
        $schedule->startTime($this->startTime);
        $schedule->endDate($this->untilDate);
        $schedule->endTime($this->endTime);
        $schedule->repeatFrequency($iso8601Interval);
        $schedule->byDay($this->weekDays);
        $schedule->repeatCount($this->getRepeatCount());

        return $schedule;
    }

    private function getRepeatCount(): ?int
    {
        $repeatCount = 0;
        for ($j = 0; $j < count($this->weekDays); $j++) {
            $weekDay = $this->weekDays[$j];
            $weekDay = $this->getWeekDayFromString($weekDay);

            if ($weekDay) {
                $repeatCount += $this->countWeekdayOccurrences($this->startDate, $this->untilDate, $weekDay);
            }
        }

        return $repeatCount;
    }

    private function getWeekDayFromString($weekDay): ?string
    {
        $weekDays = ['Monday', 'Tuesday', 'Wednesday', 'Thursday', 'Friday', 'Saturday', 'Sunday'];

        foreach ($weekDays as $day) {
            if (strpos($weekDay, $day) !== false) {
                return $day;
            }
        }

        return null;
    }

    private function countWeekdayOccurrences($startDate, $untilDate, $weekday): int
    {
        $startDateTime = new \DateTime($startDate);
        $untilDateTime = new \DateTime($untilDate);
        $untilDateTime = $untilDateTime->modify('+1 day');

        // Get all dates in range
        $dateRange = new \DatePeriod($startDateTime, new \DateInterval('P1D'), $untilDateTime);

        // Count all matching weekdays in range
        $count = 0;

        foreach ($dateRange as $date) {
            if ($date->format('l') === $weekday) {
                $count++;
            }
        }

        return $count;
    }
}
