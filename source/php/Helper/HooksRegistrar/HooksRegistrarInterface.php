<?php

namespace EventManager\Helper\HooksRegistrar;

use EventManager\Helper\Hookable;

interface HooksRegistrarInterface
{
    public function register(Hookable $object): HooksRegistrarInterface;
}
