<?php

namespace EventManager\PostToSchema\PostToEventSchema\Commands;

use PHPUnit\Framework\TestCase;
use Spatie\SchemaOrg\BaseType;

class SetDurationCommandTest extends TestCase
{
    /**
     * @testdox sets duration from start and end date
     */
    public function testExecute()
    {
        $schema = new class extends BaseType {
        };
        $schema->startDate('2021-01-01 12:00');
        $schema->endDate('2021-01-01 13:30');


        $command = new SetDuration($schema);
        $command->execute();

        $this->assertEquals('P0Y0M0DT1H30M0S', $schema->toArray()['duration']);
    }
}
