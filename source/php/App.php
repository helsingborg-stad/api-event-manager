<?php

namespace EventManager;

use EventManager\Helper\HooksRegistrar\HooksRegistrarInterface;

class App
{
    public function registerHooks(HooksRegistrarInterface $hooksRegistrar)
    {
        $postToEventSchema = new \EventManager\Helper\PostToSchema\PostToEventSchema();

        $hooksRegistrar
            ->register(new \EventManager\PostTypes\Event())
            ->register(new \EventManager\ApiResponseModifiers\Event($postToEventSchema));
    }
}
