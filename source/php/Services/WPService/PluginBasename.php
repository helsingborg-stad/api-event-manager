<?php

namespace EventManager\Services\WPService;

interface PluginBasename
{
  /**
   * Get the filesystem directory path (with trailing slash) for the plugin __FILE__ passed in.
   *
   * @return string
   */
  public function pluginBasename(string $file): string;
}
