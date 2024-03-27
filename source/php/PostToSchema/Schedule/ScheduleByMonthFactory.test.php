<?php

namespace EventManager\PostToSchema\Schedule;

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
        $occasion = [
            'date'           => '2024-01-01',
            'untilDate'      => '2024-01-17',
            'startTime'      => '13:00',
            'endTime'        => '14:00',
            'monthsInterval' => 3,
            'monthDay'       => 'day',
            'monthDayNumber' => 1,
        ];

        $schemaFactory = new ScheduleByMonthFactory();
        $schedule      = $schemaFactory->create($occasion);

        $this->assertEquals('2024-01-01', $schedule->getProperty('startDate'));
        $this->assertEquals('2024-01-17', $schedule->getProperty('endDate'));
        $this->assertEquals('13:00', $schedule->getProperty('startTime'));
        $this->assertEquals('14:00', $schedule->getProperty('endTime'));
    }

    public function testSetsRepeatFrequency()
    {
        $occasion = [
            'date'           => '2024-01-01',
            'untilDate'      => '2024-01-17',
            'startTime'      => '13:00',
            'endTime'        => '14:00',
            'monthsInterval' => 3,
            'monthDay'       => 'day',
            'monthDayNumber' => 1,
        ];

        $interval      = 3;
        $schemaFactory = new ScheduleByMonthFactory();
        $schedule      = $schemaFactory->create($occasion);

        $this->assertEquals("P{$interval}M", $schedule->getProperty('repeatFrequency'));
    }

    public function testSetsByMonthDay()
    {
        $occasion = [
            'date'           => '2024-01-01',
            'untilDate'      => '2024-01-17',
            'startTime'      => '13:00',
            'endTime'        => '14:00',
            'monthsInterval' => 1,
            'monthDay'       => 'day',
            'monthDayNumber' => 3,
        ];

        $schemaFactory = new ScheduleByMonthFactory();
        $schedule      = $schemaFactory->create($occasion);

        $this->assertEquals("3", $schedule->getProperty('byMonthDay'));
    }

    public function testSetsRepeatCountByDayNumber()
    {
        $occasion = [
            'date'           => '2024-01-01',
            'untilDate'      => '2024-04-01',
            'startTime'      => '13:00',
            'endTime'        => '14:00',
            'monthsInterval' => 1,
            'monthDay'       => 'day',
            'monthDayNumber' => 31,
        ];

        $schemaFactory = new ScheduleByMonthFactory();
        $schedule      = $schemaFactory->create($occasion);

        $this->assertEquals(2, $schedule->getProperty('repeatCount'));
    }

    /**
     * @dataProvider getInstanceInMonthData
     */
    public function testSetsRepeatCountByInstanceInMonth($occasion, $expectedRepeatCount)
    {
        $schemaFactory = new ScheduleByMonthFactory();

        $schedule = $schemaFactory->create($occasion);

        $this->assertEquals($expectedRepeatCount, $schedule->getProperty('repeatCount'));
    }

    public function getInstanceInMonthData()
    {
        return array(
            array(
                [
                    'date'            => '2024-01-01',
                    'untilDate'       => '2024-02-29',
                    'startTime'       => '13:00',
                    'endTime'         => '14:00',
                    'monthsInterval'  => 1,
                    'monthDay'        => 'second',
                    'monthDayNumber'  => null,
                    'monthDayLiteral' => 'https://schema.org/Monday'
                ], 2
            ),
            array(
                [
                    'date'            => '2024-01-01',
                    'untilDate'       => '2024-02-05',
                    'startTime'       => '13:00',
                    'endTime'         => '14:00',
                    'monthsInterval'  => 1,
                    'monthDay'        => 'first',
                    'monthDayNumber'  => null,
                    'monthDayLiteral' => 'https://schema.org/Tuesday'
                ], 1
            ),
            array(
                [
                    'date'            => '2024-01-18',
                    'untilDate'       => '2024-04-18',
                    'startTime'       => '13:00',
                    'endTime'         => '14:00',
                    'monthsInterval'  => 1,
                    'monthDay'        => 'third',
                    'monthDayNumber'  => null,
                    'monthDayLiteral' => 'https://schema.org/Wednesday'
                ], 3
            ),
            array(
                [
                    'date'            => '2024-01-03',
                    'untilDate'       => '2024-06-03',
                    'startTime'       => '13:00',
                    'endTime'         => '14:00',
                    'monthsInterval'  => 1,
                    'monthDay'        => 'last',
                    'monthDayNumber'  => null,
                    'monthDayLiteral' => 'Day'
                ], 5
            ),
            array(
                [
                    'date'            => '2024-11-18',
                    'untilDate'       => '2025-04-18',
                    'startTime'       => '13:00',
                    'endTime'         => '14:00',
                    'monthsInterval'  => 1,
                    'monthDay'        => 'third',
                    'monthDayNumber'  => null,
                    'monthDayLiteral' => 'https://schema.org/Wednesday'
                ], 6
            ),
        );
    }
}
