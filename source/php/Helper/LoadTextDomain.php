<?php

namespace EventManager\Helper;

use EventManager\HooksRegistrar\Hookable;
use WpService\Contracts\AddAction;
use WpService\Contracts\LoadPluginTextDomain;

class LoadTextDomain implements Hookable
{
    public function __construct(private string $textDomain, private AddAction&LoadPluginTextDomain $wpService)
    {
    }

    public function addHooks(): void
    {
        $this->wpService->addAction('plugins_loaded', array($this, 'loadTextDomain'));
    }

    public function loadTextDomain(): void
    {
        $this->wpService->loadPluginTextDomain($this->textDomain, false, EVENT_MANAGER_PATH . 'languages/');
    }
}
