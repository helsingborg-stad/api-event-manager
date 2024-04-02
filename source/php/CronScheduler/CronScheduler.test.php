<?php

namespace EventManager\CronScheduler;

use EventManager\Services\WPService\AddAction;
use EventManager\Services\WPService\NextScheduled;
use EventManager\Services\WPService\ScheduleEvent;
use PHPUnit\Framework\TestCase;
use WP_Error;

class CronSchedulerTest extends TestCase
{
    /**
     * @testdox Test that the CronScheduler class can be instantiated.
     */
    public function testCanBeInstantiated(): void
    {
        $wpService     = $this->getWPService();
        $cronScheduler = new CronScheduler($wpService);

        $this->assertInstanceOf(CronScheduler::class, $cronScheduler);
    }

    /**
     * @testdox addEvent() should add a CronEventInterface object to the cronEvents array.
     */
    public function testAddEvent(): void
    {
        $wpService     = $this->getWPService();
        $cronScheduler = new CronScheduler($wpService);
        $cronEvent     = $this->createMock(CronEventInterface::class);

        $cronScheduler->addEvent($cronEvent);

        $this->assertContains($cronEvent, $cronScheduler->cronEvents);
    }

    /**
     * @testdox registerEvents() should schedule an event if it is not already scheduled.
     */
    public function testRegisterEvents(): void
    {
        $wpService     = $this->getWPService();
        $cronEvent     = $this->getCronEvent();
        $cronScheduler = new CronScheduler($wpService);
        $cronScheduler->addEvent($cronEvent);

        $cronScheduler->registerEvents();

        $this->assertCount(1, $wpService->scheduledEvents);
    }

    /**
     * @testdox registerEvents() should not schedule an event if it is already scheduled.
     */
    public function testRegisterEventsAlreadyScheduled(): void
    {
        $wpService     = $this->getWPService();
        $cronEvent     = $this->getCronEvent();
        $cronScheduler = new CronScheduler($wpService);
        $cronScheduler->addEvent($cronEvent);
        $wpService->nextScheduled = 123;

        $cronScheduler->registerEvents();

        $this->assertCount(0, $wpService->scheduledEvents);
    }

    private function getCronEvent(): CronEventInterface
    {
        return new class implements CronEventInterface {
            public function getRecurrence(): string
            {
                return 'hourly';
            }

            public function getHook(): string
            {
                return 'test_hook';
            }

            public function getHookCallback(): callable
            {
                return function () {
                };
            }
        };
    }

    private function getWPService(): AddAction&NextScheduled&ScheduleEvent
    {
        return new class implements AddAction, NextScheduled, ScheduleEvent {
            public $scheduledEvents         = [];
            public int|false $nextScheduled = 0;

            public function addAction(string $tag, callable $function_to_add, int $priority = 10, int $accepted_args = 1): bool
            {
                return true;
            }

            public function nextScheduled(string $hook, array $args = []): int|false
            {
                return $this->nextScheduled;
            }

            public function scheduleEvent(int $timestamp, string $recurrence, string $hook, array $args = [], bool $wpError = false): bool|WP_Error
            {
                $this->scheduledEvents[] = [
                    'timestamp'  => $timestamp,
                    'recurrence' => $recurrence,
                    'hook'       => $hook,
                    'args'       => $args,
                    'wpError'    => $wpError,
                ];
                return true;
            }
        };
    }
}
