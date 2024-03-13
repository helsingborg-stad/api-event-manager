<?php

namespace EventManager\Services\WPService;

interface EnqueueScript
{
  public function enqueueScript(string $handle, string $src = '', array $deps = array(), string|bool|null $ver = false, array|bool $args = array(), bool $in_footer): void;
}
