<?php

namespace EventManager\Services\WPService;

interface EnqueueScript
{
  public function enqueueScript(string $handle): void;
}