<?php

namespace EventManager\PostToSchema\PostToEventSchema\Commands;

use EventManager\PostToSchema\PostToEventSchema\Commands\SetSchedule;
use EventManager\PostToSchema\Schedule\ScheduleByDayFactory;
use EventManager\PostToSchema\Schedule\ScheduleByMonthFactory;
use EventManager\PostToSchema\Schedule\ScheduleByWeekFactory;
use EventManager\PostToSchema\Schedule\NullScheduleFactory;
use PHPUnit\Framework\TestCase;
use Spatie\SchemaOrg\BaseType;

class SetScheduleTest extends TestCase
{
    public function testExecuteWithNoOccasions(): void
    {
        $schema = new class extends BaseType{
        };

        $setSchedule = new SetSchedule($schema, ['occasions' => []]);
        $setSchedule->execute();

        $this->assertArrayNotHasKey('eventSchedule', $schema->toArray());
    }

    public function testGetFactoryWithValidRepeat(): void
    {
        $setSchedule = new SetSchedule($this->createMock(BaseType::class), []);

        $this->assertInstanceOf(ScheduleByDayFactory::class, $setSchedule->getFactory('byDay'));
        $this->assertInstanceOf(ScheduleByWeekFactory::class, $setSchedule->getFactory('byWeek'));
        $this->assertInstanceOf(ScheduleByMonthFactory::class, $setSchedule->getFactory('byMonth'));
    }

    public function testGetFactoryWithInvalidRepeat(): void
    {
        $setSchedule = new SetSchedule($this->createMock(BaseType::class), []);

        $this->assertInstanceOf(NullScheduleFactory::class, $setSchedule->getFactory('invalidRepeat'));
    }
}
