<?php

namespace EventManager\PostToSchema\Schedule;

use EventManager\PostToSchema\Schedule\ScheduleByDayFactory;
use WP_Mock\Tools\TestCase;

class ScheduleByDayFactoryTest extends TestCase
{
    /**
     * @testdox Sets date and time properties to correct values.
     */
    public function testSetsDateAndTimeProperties()
    {
        $occasion = [
            'date'         => '2021-03-01',
            'untilDate'    => '2021-03-10',
            'startTime'    => '13:00',
            'endTime'      => '14:00',
            'daysInterval' => '1'
        ];

        $schemaFactory = new ScheduleByDayFactory('2021-03-01', '2021-03-10', '13:00', '14:00', '1');
        $schedule      = $schemaFactory->create($occasion);

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
        $occasion = [
            'date'         => '2021-03-01',
            'untilDate'    => '2021-03-11',
            'startTime'    => '13:00',
            'endTime'      => '14:00',
            'daysInterval' => '2'
        ];

        $schemaFactory = new ScheduleByDayFactory();
        $schedule      = $schemaFactory->create($occasion);

        $this->assertEquals('P2D', $schedule->getProperty('repeatFrequency'));
    }


    /**
     * @testdox Sets repeat count to correct number of event occurences.
     */
    public function testSetsRepeatCount()
    {
        $occasion = [
            'date'         => '2021-03-01',
            'untilDate'    => '2021-03-11',
            'startTime'    => '13:00',
            'endTime'      => '14:00',
            'daysInterval' => '2'
        ];

        $schemaFactory = new ScheduleByDayFactory();
        $schedule      = $schemaFactory->create($occasion);
        $scheduleArray = $schedule->toArray();

        $this->assertEquals(6, $scheduleArray['repeatCount']);
    }
}
