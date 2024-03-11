<?php

namespace EventManager\Tests\PostToSchema\Schedule;

use EventManager\PostToSchema\Schedule\ScheduleByMonthFactory;
use WP_Mock\Tools\TestCase;

class ScheduleByMonthFactoryTest extends TestCase
{
    public function testClassExists()
    {
        $this->assertTrue(class_exists('EventManager\PostToSchema\Schedule\ScheduleByMonthFactory'));
    }

    public function testSetsStartAndEndDateAndTime()
    {
        $interval      = 3;
        $schemaFactory = new ScheduleByMonthFactory('2024-01-01', '2024-01-17', '13:00', '14:00', $interval, 'day', 1);
        $schedule      = $schemaFactory->create();

        $this->assertEquals('2024-01-01', $schedule->getProperty('startDate'));
        $this->assertEquals('2024-01-17', $schedule->getProperty('endDate'));
        $this->assertEquals('13:00', $schedule->getProperty('startTime'));
        $this->assertEquals('14:00', $schedule->getProperty('endTime'));
    }

    public function testSetsRepeatFrequency()
    {
        $interval      = 3;
        $schemaFactory = new ScheduleByMonthFactory('2024-01-01', '2024-01-17', '13:00', '14:00', $interval, 'day', 1);
        $schedule      = $schemaFactory->create();

        $this->assertEquals("P{$interval}M", $schedule->getProperty('repeatFrequency'));
    }

    public function testSetsByMonthDay()
    {
        $schemaFactory = new ScheduleByMonthFactory('2024-01-01', '2024-01-17', '13:00', '14:00', 1, 'day', 3);
        $schedule      = $schemaFactory->create();

        $this->assertEquals("3", $schedule->getProperty('byMonthDay'));
    }

    public function testSetsRepeatCountByDayNumber()
    {
        $monthDayNumber = 31;
        $schemaFactory  = new ScheduleByMonthFactory('2024-01-01', '2024-04-01', '13:00', '14:00', 1, 'day', $monthDayNumber);
        $schedule       = $schemaFactory->create();

        $this->assertEquals(2, $schedule->getProperty('repeatCount'));
    }

    /**
     * @dataProvider getInstanceInMonthData
     */
    public function testSetsRepeatCountByInstanceInMonth($params, $expectedRepeatCount)
    {
        $schemaFactory = new ScheduleByMonthFactory(
            $params['startDate'],
            $params['endDate'],
            $params['startTime'],
            $params['endTime'],
            $params['interval'],
            $params['monthDay'],
            $params['monthDayNumber'],
            $params['monthDayLiteral']
        );

        $schedule = $schemaFactory->create();

        $this->assertEquals($expectedRepeatCount, $schedule->getProperty('repeatCount'));
    }

    public function getInstanceInMonthData()
    {
        return array(
            array(
                [
                    'startDate'       => '2024-01-01',
                    'endDate'         => '2024-02-29',
                    'startTime'       => '13:00',
                    'endTime'         => '14:00',
                    'interval'        => 1,
                    'monthDay'        => 'second',
                    'monthDayNumber'  => null,
                    'monthDayLiteral' => 'https://schema.org/Monday'
                ], 2
            ),
            array(
                [
                    'startDate'       => '2024-01-01',
                    'endDate'         => '2024-02-05',
                    'startTime'       => '13:00',
                    'endTime'         => '14:00',
                    'interval'        => 1,
                    'monthDay'        => 'first',
                    'monthDayNumber'  => null,
                    'monthDayLiteral' => 'https://schema.org/Tuesday'
                ], 1
            ),
            array(
                [
                    'startDate'       => '2024-01-18',
                    'endDate'         => '2024-04-18',
                    'startTime'       => '13:00',
                    'endTime'         => '14:00',
                    'interval'        => 1,
                    'monthDay'        => 'third',
                    'monthDayNumber'  => null,
                    'monthDayLiteral' => 'https://schema.org/Wednesday'
                ], 3
            ),
            array(
                [
                    'startDate'       => '2024-01-03',
                    'endDate'         => '2024-06-03',
                    'startTime'       => '13:00',
                    'endTime'         => '14:00',
                    'interval'        => 1,
                    'monthDay'        => 'last',
                    'monthDayNumber'  => null,
                    'monthDayLiteral' => 'Day'
                ], 5
            ),
            array(
                [
                    'startDate'       => '2024-11-18',
                    'endDate'         => '2025-04-18',
                    'startTime'       => '13:00',
                    'endTime'         => '14:00',
                    'interval'        => 1,
                    'monthDay'        => 'third',
                    'monthDayNumber'  => null,
                    'monthDayLiteral' => 'https://schema.org/Wednesday'
                ], 6
            ),
        );
    }
}
