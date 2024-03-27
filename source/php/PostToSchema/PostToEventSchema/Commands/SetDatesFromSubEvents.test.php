<?php

namespace EventManager\PostToSchema\PostToEventSchema\Commands;

use PHPUnit\Framework\TestCase;
use Spatie\SchemaOrg\BaseType;

class SetDatesFromSubEventsTest extends TestCase
{
    /**
     * @testdox sets the start and end date of the event based on the sub events
     */
    public function testExecute()
    {
        $subEvent1 = $this->getNewBasetype();
        $subEvent2 = $this->getNewBasetype();
        $schema    = $this->getNewBasetype();
        $subEvent1->startDate('2024-01-01 13:00');
        $subEvent1->endDate('2024-01-01 15:00');
        $subEvent2->startDate('2024-02-01 18:00');
        $subEvent2->endDate('2024-02-01 20:00');

        $schema->subEvents([$subEvent1, $subEvent2]);
        $command = new SetDatesFromSubEvents($schema);
        $command->execute();

        $this->assertEquals('2024-01-01 13:00', $schema->toArray()['startDate']);
        $this->assertEquals('2024-02-01 20:00', $schema->toArray()['endDate']);
    }

    private function getNewBasetype(): BaseType
    {
        return new class extends BaseType{
        };
    }
}
