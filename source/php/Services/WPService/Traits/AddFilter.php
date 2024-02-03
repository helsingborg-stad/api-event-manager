<?php

namespace EventManager\Services\WPService\Traits;

trait AddFilter
{
    public function addFilter(string $tag, callable $function_to_add, int $priority = 10, int $accepted_args = 1): bool
    {
        return add_filter($tag, $function_to_add, $priority, $accepted_args);
    }
}
