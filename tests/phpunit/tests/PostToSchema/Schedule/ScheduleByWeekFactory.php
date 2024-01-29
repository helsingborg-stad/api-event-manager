<?php

namespace EventManager\Tests\PostToSchema\Schedule;

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

        $this->assertEquals('2024-01-01', $schedule->getProperty('startDate'));
        $this->assertEquals('2024-01-17', $schedule->getProperty('endDate'));
        $this->assertEquals('13:00', $schedule->getProperty('startTime'));
        $this->assertEquals('14:00', $schedule->getProperty('endTime'));
        $this->assertCount(2, $schedule->getProperty('byDay'));
        $this->assertContains('https://schema.org/Monday', $schedule->getProperty('byDay'));
        $this->assertContains('https://schema.org/Wednesday', $schedule->getProperty('byDay'));
        $this->assertEquals('P1W', $schedule->getProperty('repeatFrequency'));
        $this->assertEquals(6, $schedule->getProperty('repeatCount'));
    }
}
