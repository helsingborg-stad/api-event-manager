<?php

namespace EventManager\PostToSchema\Schedule;

use DateTime;

class ScheduleByMonthFactory implements ScheduleFactory
{
    private string $startDate;
    private string $untilDate;
    private string $startTime;
    private string $endTime;
    private string|int $interval;
    private string $monthDay;
    private string|int|null $monthDayNumber = null;
    private ?string $monthDayLiteral        = null;

    public function create(array $occasion): ?\Spatie\SchemaOrg\Schedule
    {
        $this->startDate       = $occasion['date'];
        $this->untilDate       = $occasion['untilDate'];
        $this->startTime       = $occasion['startTime'];
        $this->endTime         = $occasion['endTime'];
        $this->interval        = $occasion['monthsInterval'] ?: 1;
        $this->monthDay        = $occasion['monthDay'] ?: null;
        $this->monthDayNumber  = $occasion['monthDayNumber'] ?: null;
        $this->monthDayLiteral = $occasion['monthDayLiteral'] ?? null;

        $iso8601Interval = "P{$this->interval}M";

        $schedule = new \Spatie\SchemaOrg\Schedule();
        $schedule->startDate($this->startDate);
        $schedule->startTime($this->startTime);
        $schedule->endDate($this->untilDate);
        $schedule->endTime($this->endTime);
        $schedule->repeatFrequency($iso8601Interval);
        $schedule->byMonthDay($this->getByMonthday());
        $schedule->repeatCount($this->getRepeatCount());

        return $schedule;
    }

    private function getByMonthday(): ?int
    {
        if ($this->monthDay === 'day') {
            return (int)$this->monthDayNumber ?: null;
        }

        return null;
    }

    private function getRepeatCount(): ?int
    {
        if ($this->monthDay === 'day') {
            $dayOfMonthNumber = (int)$this->monthDayNumber;
            $start            = new \DateTime($this->startDate);
            $end              = new \DateTime($this->untilDate);
            $end              = $end->modify('+1 day');
            $interval         = new \DateInterval("P1D");
            $period           = new \DatePeriod($start, $interval, $end);
            $count            = 0;

            foreach ($period as $date) {
                if ((int)$date->format('j') === $dayOfMonthNumber) {
                    $count++;
                }
            }

            return $count;
        } else {
            $count = $this->countNumberOfOccurencesByPlaceInMonth();
            return $count;
        }

        return null;
    }

    private function countNumberOfOccurencesByPlaceInMonth(): ?int
    {
        $dayOfWeek = $this->getDayOfWeek();

        if ($dayOfWeek === null) {
            return null;
        }

        // Get the first day of the month
        $startDate = new \DateTimeImmutable($this->startDate);
        $endDate   = new \DateTimeImmutable($this->untilDate);

        // For each month in range
        $interval             = new \DateInterval("P1M");
        $firstDayOfStartMonth = $startDate->modify('first day of this month');
        $lastDayOfEndMonth    = $endDate->modify('last day of this month');
        $period               = new \DatePeriod($firstDayOfStartMonth, $interval, $lastDayOfEndMonth);
        $count                = 0;

        foreach ($period as $date) {
            $modifier   = "{$this->monthDay} {$dayOfWeek} of this month";
            $dayOfMonth = (new DateTime($date->format('Y-m-d')))->modify($modifier);

            if (
                $dayOfMonth->format('Y-m-d') >= $startDate->format('Y-m-d') &&
                $dayOfMonth->format('Y-m-d') <= $endDate->format('Y-m-d')
            ) {
                $count++;
            }
        }

        return $count;
    }

    private function getDayOfWeek()
    {
        $weekDays = ['Monday', 'Tuesday', 'Wednesday', 'Thursday', 'Friday', 'Saturday', 'Sunday'];

        switch ($this->monthDayLiteral) {
            case 'Day':
                return $this->monthDayLiteral;
                break;
            default:
                foreach ($weekDays as $day) {
                    if (strpos($this->monthDayLiteral, $day) !== false) {
                        return $day;
                    }
                }
                break;
        }

        return null;
    }
}
