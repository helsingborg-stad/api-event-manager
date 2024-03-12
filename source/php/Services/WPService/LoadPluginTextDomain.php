<?php

namespace EventManager\Services\WPService;

interface LoadPluginTextDomain
{
    /**
     * Loads the translation files for a plugin's text domain.
     *
     * @param string $domain The text domain of the plugin.
     * @param string $path The path to the translation files.
     * @param string $relativeTo The base path to resolve the relative path to the translation files.
     * @return void
     */
    public function loadPluginTextDomain(string $domain, string $path, string $relativeTo): void;
}
