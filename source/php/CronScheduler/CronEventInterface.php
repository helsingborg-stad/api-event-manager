<?php

namespace EventManager\CronScheduler;

interface CronEventInterface
{
    public function getRecurrence(): string;
    public function getHook(): string;
    public function getHookCallback(): callable;
}
