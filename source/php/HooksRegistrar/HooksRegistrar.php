<?php

namespace EventManager\HooksRegistrar;

use EventManager\Helper\Hookable;

class HooksRegistrar implements HooksRegistrarInterface
{
    public function register(Hookable $object): HooksRegistrarInterface
    {
        $object->addHooks();

        return $this;
    }
}
