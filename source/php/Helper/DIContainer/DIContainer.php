<?php

namespace EventManager\Helper\DIContainer;

interface DIContainer
{
    public function bind(string $name, mixed $value): DIContainer;
    public function get(string $name): mixed;
}
