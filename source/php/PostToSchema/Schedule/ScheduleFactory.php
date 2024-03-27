<?php

namespace EventManager\PostToSchema\Schedule;

interface ScheduleFactory
{
    public function create(array $occasion): ?\Spatie\SchemaOrg\Schedule;
}
