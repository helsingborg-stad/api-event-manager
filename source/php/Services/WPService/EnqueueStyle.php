<?php

namespace EventManager\Services\WPService;

interface EnqueueStyle
{
  public function enqueueStyle(string $handle, string $src = '', array $deps = array(), string|bool|null $ver = false, string $media = 'all'): void;
}
