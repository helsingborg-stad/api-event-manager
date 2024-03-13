<?php

namespace EventManager\Services\AcfService\Functions;

interface RenderFieldSetting
{
    public function renderFieldSetting(array $field, array $configuration, bool $global = false): void;
}
