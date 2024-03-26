<?php

namespace EventManager\PostToSchema\PostToEventSchema\Commands;

use PHPUnit\Framework\TestCase;
use Spatie\SchemaOrg\BaseType;

class SetDatesCommandTest extends TestCase
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
                    'repeat'    => 'no',
                    'date'      => '2021-01-01',
                    'startTime' => '12:00',
                    'endTime'   => '13:00'
                ]
            ]
        ];

        $command = new SetDatesCommand($schema, $meta);
        $command->execute();

        $this->assertEquals('2021-01-01 12:00', $schema->toArray()['startDate']);
        $this->assertEquals('2021-01-01 13:00', $schema->toArray()['endDate']);
    }
}
