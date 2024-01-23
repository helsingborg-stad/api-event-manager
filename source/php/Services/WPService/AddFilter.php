<?php

namespace EventManager\Services\WPService;

interface AddFilter
{
    public function addFilter(string $tag, callable $function_to_add, int $priority = 10, int $accepted_args = 1): bool;
}
