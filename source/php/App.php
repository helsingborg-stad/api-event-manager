<?php

namespace EventManager;

use EventManager\Helper\HooksRegistrar\HooksRegistrarInterface;

class App
{
    public function registerHooks(HooksRegistrarInterface $hooksRegistrar)
    {
        $hooksRegistrar
            ->register(new \EventManager\HideUnusedAdminPages())
            ->register(new \EventManager\PostTypes\Event())
            ->register(new \EventManager\PostTypes\Organization())
            ->register(new \EventManager\Taxonomies\Audience())
            ->register(new \EventManager\ApiResponseModifiers\Event());
    }
}
