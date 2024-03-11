<?php

namespace EventManager\Services\WPService;

/**
 * Interface ApplyFilters
 *
 * This interface defines the contract for applying filters in WordPress.
 */
interface ApplyFilters
{
    /**
     * Apply filters to a value.
     *
     * @param string $hook_name The name of the filter hook.
     * @param mixed $value The value to be filtered.
     * @param mixed $args Additional arguments passed to the filter hook.
     * @return mixed The filtered value.
     */
    public function applyFilters(string $hook_name, mixed $value, mixed $args): mixed;
}
