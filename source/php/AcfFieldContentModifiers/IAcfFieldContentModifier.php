<?php

namespace EventManager\AcfFieldContentModifiers;

interface IAcfFieldContentModifier
{
    public function modifyFieldContent(array $field): array;
    public function getFieldKey(): string;
}
