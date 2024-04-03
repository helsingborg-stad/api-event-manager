<?php

namespace EventManager\Services\WPService;

interface GetEnvironmentType
{
  /**
   * Check whether the current user is an administrator.
   *
   * @return bool
   */
  public function getEnvironmentType(): string;
}
