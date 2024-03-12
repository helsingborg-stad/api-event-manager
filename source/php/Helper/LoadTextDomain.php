<?php

namespace EventManager\Helper;

use EventManager\Services\WPService\WPService;

class LoadTextDomain implements Hookable
{
    public function __construct(private WPService $wpService)
    {
    }

    public function addHooks(): void
    {
        add_action('plugins_loaded', array($this, 'loadTextDomain'));
    }

    public function loadTextDomain(): void
    {
        $this->wpService->loadPluginTextDomain('api-event-manager', false, EVENT_MANAGER_PATH . 'languages/');
    }
}
