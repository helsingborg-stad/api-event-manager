<?php

namespace EventManager\PostToSchema\PostToEventSchema\Commands;

use PHPUnit\Framework\TestCase;
use Spatie\SchemaOrg\BaseType;

class SetDatesTest extends TestCase
{
    /**
     * @testdox sets dates from occasions
     */
    public function testExecute()
    {
        $schema = new class extends BaseType {
        };
        $meta   = [
            'occasions' => [
                [
                    'date'      => '2021-01-01',
                    'startTime' => '12:00',
                    'endTime'   => '13:00'
                ]
            ]
        ];

        $command = new SetDates($schema, $meta);
        $command->execute();

        $this->assertEquals('2021-01-01 12:00', $schema->toArray()['startDate']);
        $this->assertEquals('2021-01-01 13:00', $schema->toArray()['endDate']);
    }

    /**
     * @testdox sets start date from earliest and end date from latest occasion
     */
    public function testExecuteWithMultipleOccasions()
    {
        $schema = new class extends BaseType {
        };
        $meta   = [
            'occasions' => [
                [
                    'date'      => '2021-01-01',
                    'untilDate' => '2021-01-03',
                    'startTime' => '12:00',
                    'endTime'   => '13:00'
                ],
                [
                    'date'      => '2021-02-03',
                    'untilDate' => '2021-02-04',
                    'startTime' => '12:00',
                    'endTime'   => '20:00'
                ]
            ]
        ];

        $command = new SetDates($schema, $meta);
        $command->execute();

        $this->assertEquals('2021-01-01 12:00', $schema->toArray()['startDate']);
        $this->assertEquals('2021-02-04 20:00', $schema->toArray()['endDate']);
    }
}
