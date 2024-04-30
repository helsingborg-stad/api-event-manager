<?php

namespace EventManager\Modifiers;

use EventManager\HooksRegistrar\Hookable;
use WpService\WpService;

class DisableGutenbergEditor implements Hookable
{
    public function __construct(private WPService $wpService)
    {
    }

    public function addHooks(): void
    {
        $this->wpService->addFilter('use_block_editor_for_post_type', '__return_false');
    }
}
