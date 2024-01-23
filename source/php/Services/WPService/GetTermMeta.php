<?php

namespace EventManager\Services\WPService;

interface GetTermMeta
{
    public function getTermMeta(int $term_id, string $key = '', bool $single = false): mixed;
}
