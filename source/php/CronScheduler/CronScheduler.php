<?php

namespace EventManager\CronScheduler;

use EventManager\Helper\Hookable;
use WpService\Contracts\AddAction;
use WpService\Contracts\NextScheduled;
use WpService\Contracts\ScheduleEvent;

class CronScheduler implements Hookable
{
    public $cronEvents = [];

    public function __construct(private AddAction&NextScheduled&ScheduleEvent $wpService)
    {
    }

    public function addHooks(): void
    {
        $this->wpService->addAction('init', [$this, 'registerEvents']);
        $this->wpService->addAction('init', [$this, 'registerCronEventHooks']);
    }

    public function registerCronEventHooks(): void
    {
        foreach ($this->cronEvents as $cronEvent) {
            $this->wpService->addAction($cronEvent->getHook(), $cronEvent->getHookCallback());
        }
    }

    public function addEvent(CronEventInterface $cronEvent): void
    {
        $this->cronEvents[] = $cronEvent;
    }

    public function registerEvents(): void
    {
        foreach ($this->cronEvents as $cronEvent) {
            if ($this->wpService->nextScheduled($cronEvent->getHook())) {
                continue;
            }

            $this->wpService->scheduleEvent(time(), $cronEvent->getRecurrence(), $cronEvent->getHook());
        }
    }
}
