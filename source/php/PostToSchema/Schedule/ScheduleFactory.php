<?php

namespace EventManager\PostToSchema\Schedule;

interface ScheduleFactory
{
    public function create(): ?\Spatie\SchemaOrg\Schedule;
}
