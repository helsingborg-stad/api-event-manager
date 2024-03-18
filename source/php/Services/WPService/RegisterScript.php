<?php

namespace EventManager\Services\WPService;

interface RegisterScript
{
  public function registerScript(string $handle, string $src = '', array $deps = array(), string|bool|null $ver = false, bool $in_footer = true): void;
}