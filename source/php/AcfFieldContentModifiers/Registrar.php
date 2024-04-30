<?php

namespace EventManager\AcfFieldContentModifiers;

use EventManager\HooksRegistrar\Hookable;
use EventManager\AcfFieldContentModifiers\IAcfFieldContentModifier;
use WpService\Contracts\AddAction;

class Registrar implements Hookable
{
    /**
     * @param IAcfFieldContentModifier[] $modifiers
     * @param AddAction $wpService
     */
    public function __construct(private array $modifiers, private AddAction $wpService)
    {
    }

    public function addHooks(): void
    {
        foreach ($this->modifiers as $modifier) {
            $this->wpService->addAction(
                "acf/load_field/key={$modifier->getFieldKey()}",
                [$modifier, 'modifyFieldContent']
            );
        }
    }
}
