<?php

namespace EventManager\HooksRegistrar;

use EventManager\HooksRegistrar\Hookable;

class HooksRegistrar implements HooksRegistrarInterface
{
    public function register(Hookable $object): HooksRegistrarInterface
    {
        $object->addHooks();

        return $this;
    }
}
