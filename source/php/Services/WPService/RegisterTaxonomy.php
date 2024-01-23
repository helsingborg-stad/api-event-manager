<?php

namespace EventManager\Services\WPService;

interface RegisterTaxonomy
{
    public function registerTaxonomy(string $taxonomy, array|string $objectType, array|string $args = []): void;
}
