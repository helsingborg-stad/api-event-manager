<?php

namespace EventManager\CronScheduler;

use EventManager\HooksRegistrar\Hookable;
use WpService\Contracts\AddAction;
use WpService\Contracts\WpNextScheduled;
use WpService\Contracts\WpScheduleEvent;

class CronScheduler implements CronSchedulerInterface, Hookable
{
    /**
     * @var CronEventInterface[]
     */
    public $cronEvents = [];

    public function __construct(private AddAction&WpNextScheduled&WpScheduleEvent $wpService)
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
            if ($this->wpService->WpNextScheduled($cronEvent->getHook())) {
                continue;
            }

            $this->wpService->WpScheduleEvent(time(), $cronEvent->getRecurrence(), $cronEvent->getHook());
        }
    }
}
