<?php

namespace EventManager\AcfSavepostActions;

use EventManager\Helper\Hookable;
use WpService\Contracts\AddAction;

class Registrar implements Hookable
{
    /**
     * @param IAcfFieldSavePostAction[] $modifiers
     * @param AddAction $wpService
     */
    public function __construct(private array $actions, private AddAction $wpService)
    {
    }

    public function addHooks(): void
    {
        foreach ($this->actions as $modifier) {
            $this->wpService->addAction("acf/save_post", [$modifier, 'savePost']);
        }
    }
}
