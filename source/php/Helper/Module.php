<?php

namespace EventManager\Helper;

use EventManager\Services\WPService\WPService;

abstract class Module implements Hookable
{
    private WPService $wp;

    abstract public function register(): void;

    public function __construct(WPService $wpService)
    {
        $this->wp = $wpService;
    }

    public function addHooks(): void
    {
        $this->wp->addAction('init', [$this, 'register']);
    }
}
