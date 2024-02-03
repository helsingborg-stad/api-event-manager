<?php

namespace EventManager\Services\WPService\Traits;

trait AddAction
{
    public function addAction(string $tag, callable $function_to_add, int $priority = 10, int $accepted_args = 1): bool
    {
        return add_action($tag, $function_to_add, $priority, $accepted_args);
    }
}
