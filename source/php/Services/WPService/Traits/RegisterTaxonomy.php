<?php

namespace EventManager\Services\WPService\Traits;

trait RegisterTaxonomy
{
    public function registerTaxonomy(string $taxonomy, array|string $objectType, array|string $args = []): void
    {
        register_taxonomy($taxonomy, $objectType, $args);
    }
}
