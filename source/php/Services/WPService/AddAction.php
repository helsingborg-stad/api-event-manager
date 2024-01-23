<?php

namespace EventManager\Services\WPService;

interface AddAction
{
    public function addAction(string $tag, callable $function_to_add, int $priority = 10, int $accepted_args = 1): bool;
}
