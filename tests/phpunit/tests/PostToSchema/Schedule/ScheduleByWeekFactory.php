<?php

namespace EventManager\Tests\PostToSchema\Schedule;

use EventManager\PostToSchema\Schedule\ScheduleByDayFactory;
use EventManager\PostToSchema\Schedule\ScheduleByWeekFactory;
use WP_Mock\Tools\TestCase;

class ScheduleByWeekFactoryTest extends TestCase
{
    /**
     * @testdox Sets expectedProperties.
     */
    public function testSetsExpectedProperties()
    {
        $weekDays      = [ 'https://schema.org/Monday', 'https://schema.org/Wednesday', ];
        $schemaFactory = new ScheduleByWeekFactory('2024-01-01', '2024-01-17', '13:00', '14:00', '1', $weekDays);
        $schedule      = $schemaFactory->create();
        $scheduleArray = $schedule->toArray();

        $this->assertEquals('2024-01-01', $scheduleArray['startDate']);
        $this->assertEquals('2024-01-17', $scheduleArray['endDate']);
        $this->assertEquals('13:00', $scheduleArray['startTime']);
        $this->assertEquals('14:00', $scheduleArray['endTime']);
        $this->assertCount(2, $scheduleArray['byDay']);
        $this->assertContains('https://schema.org/Monday', $scheduleArray['byDay']);
        $this->assertContains('https://schema.org/Wednesday', $scheduleArray['byDay']);
        $this->assertEquals('P1W', $scheduleArray['repeatFrequency']);
        $this->assertEquals(6, $scheduleArray['repeatCount']);
    }
}
