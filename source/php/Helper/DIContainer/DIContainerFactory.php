<?php

namespace EventManager\Helper\DIContainer;

class DIContainerFactory
{
    public static function create(): DIContainer
    {
        return new class () implements DIContainer
        {
            private $diContainer;

            public function __construct()
            {
                $this->diContainer = new \DI\Container();
            }

            public function bind(string $name, mixed $value): DIContainer
            {
                $this->diContainer->set($name, $value);
                return $this;
            }

            public function get(string $name): mixed
            {
                return $this->diContainer->get($name);
            }
        };
    }
}
