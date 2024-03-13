<?php

namespace EventManager\Services\WPService;

interface IsAdmin
{
  /**
   * Check whether the current user is an administrator.
   *
   * @return bool
   */
  public function is_admin(): bool;
}
