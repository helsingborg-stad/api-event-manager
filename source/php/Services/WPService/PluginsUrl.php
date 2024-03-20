<?php

namespace EventManager\Services\WPService;

interface PluginsUrl
{
  /**
   * Retrieves a URL within the plugins or mu-plugins directory.
   *
   * @return string
   */
  public function pluginsUrl(string $path = '', string $plugin = ''): string;
}
