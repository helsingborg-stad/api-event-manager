<?php

namespace EventManager\Services\WPService\Traits;

trait ApplyFilters
{
    /**
     * Apply filters to a value using the specified hook name and arguments.
     *
     * @param string $hook_name The name of the hook to apply filters to.
     * @param mixed $value The value to apply filters to.
     * @param mixed $args Additional arguments to pass to the filters.
     * @return mixed The filtered value.
     */
    public function applyFilters(string $hook_name, mixed $value, mixed $args): mixed
    {
        return apply_filters($hook_name, $value, $args);
    }
}
