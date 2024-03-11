<?php

namespace EventManager\Services\WPService\Traits;

trait GetTermMeta
{
    public function getTermMeta(int $term_id, string $key = '', bool $single = false): mixed
    {
        return get_term_meta($term_id, $key, $single);
    }
}
