<?php

namespace EventManager\Services\WPService;

interface EnqueueStyle
{
  public function enqueueStyle(string $handle): void;
}
