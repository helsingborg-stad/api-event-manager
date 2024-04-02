<?php

namespace EventManager\Services\WPService;

use WP_Error;

interface ScheduleEvent
{
    /**
     * Schedules an event.
     *
     * @param int $timestamp The timestamp when the event should be scheduled.
     * @param string $recurrence The recurrence pattern for the event.
     * @param string $hook The hook name for the event.
     * @param array $args Optional arguments for the event.
     * @param bool $wpError Whether to return a WP_Error object on failure.
     * @return bool|WP_Error True on success, WP_Error object on failure if $wpError is true.
     */
    public function scheduleEvent(
        int $timestamp,
        string $recurrence,
        string $hook,
        array $args = [],
        bool $wpError = false
    ): bool|WP_Error;
}
