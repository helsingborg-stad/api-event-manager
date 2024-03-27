<?php

namespace EventManager\PostToSchema\Schedule;

class NullScheduleFactory implements ScheduleFactory
{
    public function create(array $occasion): ?\Spatie\SchemaOrg\Schedule
    {
        return null;
    }
}
