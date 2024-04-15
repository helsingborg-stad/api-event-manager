<?php

namespace EventManager\AcfFieldContentModifiers;

use EventManager\Helper\Hookable;
use EventManager\Services\WPService\WPService;
use EventManager\AcfFieldContentModifiers\IAcfFieldContentModifier;

abstract class AcfSelectFieldContentModifier implements Hookable, IAcfFieldContentModifier
{
    public function __construct(private WPService $wpService){
        $this->addHooks();
    }

    public function addHooks(): void
    {
        $this->wpService->addAction(
            'acf/load_field/key=' . $this->getFieldKey(), 
            [$this, 'filterField']
        );
    }

    public function filterField($field, $method = null): array
    {
        $field['choices'] = $this->getFieldValue() ?? [];
        return $field;
    }

    abstract public function getFieldKey(): string;

    abstract public function getFieldValue(): array;
}
