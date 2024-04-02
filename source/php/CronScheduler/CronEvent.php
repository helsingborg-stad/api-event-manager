<?php

namespace EventManager\CronScheduler;

class CronEvent implements CronEventInterface
{
    /**
     * Represents a cron event.
     *
     * @param string $recurrence The recurrence pattern of the event.
     * @param string $hook The hook name associated with the event.
     * @param callable $hookCallback The callback function to be executed when the event is triggered.
     */
    public function __construct(private string $recurrence, private string $hook, private $hookCallback)
    {
    }

    public function getRecurrence(): string
    {
        return $this->recurrence;
    }

    public function getHook(): string
    {
        return $this->hook;
    }

    public function getHookCallback(): callable
    {
        return $this->hookCallback;
    }
}
