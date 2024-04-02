<?php

namespace EventManager\HooksRegistrar;

use EventManager\Helper\Hookable;

interface HooksRegistrarInterface
{
    public function register(Hookable $object): HooksRegistrarInterface;
}
