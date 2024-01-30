<?php

namespace EventManager;

use EventManager\Helper\HooksRegistrar\HooksRegistrarInterface;
use EventManager\Services\WPService\WPService;

class App
{
    private WPService $wpService;

    public function __construct(WPService $wpService)
    {
        $this->wpService = $wpService;
    }

    public function registerHooks(HooksRegistrarInterface $hooksRegistrar)
    {
        $hooksRegistrar
            ->register(new \EventManager\HideUnusedAdminPages($this->wpService))
            ->register(new \EventManager\PostTypes\Event($this->wpService))
            ->register(new \EventManager\Taxonomies\Audience($this->wpService))
            ->register(new \EventManager\Taxonomies\Organization($this->wpService))
            ->register(new \EventManager\ApiResponseModifiers\Event());
    }
}
