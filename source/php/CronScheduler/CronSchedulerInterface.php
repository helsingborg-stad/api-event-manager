<?php

namespace EventManager\CronScheduler;

interface CronSchedulerInterface
{
    public function addEvent(CronEventInterface $cronEvent): void;
}
