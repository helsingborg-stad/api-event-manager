<?php

namespace EventManager\Modifiers;

use EventManager\HooksRegistrar\Hookable;
use WpService\Contracts\AddFilter;

class DisableGutenbergEditor implements Hookable
{
    public function __construct(private AddFilter $wpService)
    {
    }

    public function addHooks(): void
    {
        $this->wpService->addFilter('use_block_editor_for_post_type', '__return_false');
    }
}
