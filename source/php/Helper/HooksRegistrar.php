<?php

namespace EventManager\Helper;

use EventManager\Helper\HooksRegistrar\HooksRegistrarInterface;

class HooksRegistrar implements HooksRegistrarInterface
{
    public function register(Hookable $object): HooksRegistrarInterface
    {
        $object->addHooks();

        return $this;
    }
}
