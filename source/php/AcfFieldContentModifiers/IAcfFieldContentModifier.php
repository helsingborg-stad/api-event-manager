<?php

namespace EventManager\AcfFieldContentModifiers;

interface IAcfFieldContentModifier 
{
    public function addHooks(): void; 
    public function filterField(array $field): array;
    public function getFieldKey(): string;
    public function getFieldValue(): array|string;
}
