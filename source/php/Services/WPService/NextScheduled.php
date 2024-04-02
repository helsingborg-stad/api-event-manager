<?php

namespace EventManager\Services\WPService;

interface NextScheduled
{
    /**
     * Retrieves the timestamp for the next scheduled occurrence of a specific hook.
     *
     * @param string $hook The name of the hook.
     * @param array $args Optional arguments to pass to the hook.
     * @return int|false The timestamp of the next scheduled occurrence, or false if not found.
     */
    public function nextScheduled(string $hook, array $args = []): int|false;
}
