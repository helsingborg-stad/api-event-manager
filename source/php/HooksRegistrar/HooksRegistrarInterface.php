<?php

namespace EventManager\HooksRegistrar;

use EventManager\HooksRegistrar\Hookable;

interface HooksRegistrarInterface
{
    public function register(Hookable $object): HooksRegistrarInterface;
}
