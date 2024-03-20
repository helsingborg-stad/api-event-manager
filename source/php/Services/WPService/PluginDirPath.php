<?php

namespace EventManager\Services\WPService;

interface PluginDirPath
{
  /**
   * Get the filesystem directory path (with trailing slash) for the plugin __FILE__ passed in.
   *
   * @return string
   */
  public function pluginDirPath(string $file): string;
}
