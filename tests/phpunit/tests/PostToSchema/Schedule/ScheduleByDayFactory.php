<?php

namespace EventManager\Tests\PostToSchema\Schedule;

use EventManager\PostToSchema\Schedule\ScheduleByDayFactory;
use WP_Mock\Tools\TestCase;

class ScheduleByDayFactoryTest extends TestCase
{
    /**
     * @testdox Sets date and time properties to correct values.
     */
    public function testSetsDateAndTimeProperties()
    {
        $schemaFactory = new ScheduleByDayFactory('2021-03-01', '2021-03-10', '13:00', '14:00', '1');
        $schedule      = $schemaFactory->create();

        $this->assertEquals('2021-03-01', $schedule->getProperty('startDate'));
        $this->assertEquals('2021-03-10', $schedule->getProperty('endDate'));
        $this->assertEquals('13:00', $schedule->getProperty('startTime'));
        $this->assertEquals('14:00', $schedule->getProperty('endTime'));
    }

    /**
     * @testdox Sets repeat frequency to correct ISO8601 interval.
     */
    public function testSetsFrequency()
    {
        $schemaFactory = new ScheduleByDayFactory('2021-03-01', '2021-03-11', '13:00', '14:00', '2');
        $schedule      = $schemaFactory->create();

        $this->assertEquals('P2D', $schedule->getProperty('repeatFrequency'));
    }


    /**
     * @testdox Sets repeat count to correct number of event occurences.
     */
    public function testSetsRepeatCount()
    {
        $schemaFactory = new ScheduleByDayFactory('2021-03-01', '2021-03-11', '13:00', '14:00', '2');
        $schedule      = $schemaFactory->create();
        $scheduleArray = $schedule->toArray();

        $this->assertEquals(6, $scheduleArray['repeatCount']);
    }
}
