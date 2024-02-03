<?php

namespace EventManager\Services\WPService\Traits;

trait IsWPError
{
    public function isWPError(mixed $thing): bool
    {
        return is_wp_error($thing);
    }
}
