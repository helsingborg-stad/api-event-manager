<?php

namespace EventManager\Services\WPService;

interface RegisterStyle
{
  public function registerStyle(string $handle, string $src = '', array $deps = array(), string|bool|null $ver = false, string $media = 'all'): void;
}
