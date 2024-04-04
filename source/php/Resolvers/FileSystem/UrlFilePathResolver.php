<?php

  namespace EventManager\Resolvers\FileSystem;

use EventManager\Services\WPService\PluginBasename;
use EventManager\Services\WPService\PluginsUrl;

class UrlFilePathResolver implements FilePathResolverInterface
{
    public function __construct(private PluginBasename&PluginsUrl $wpService)
    {
    }

    public function resolve(string $filePath): string
    {
        return $this->wpService->pluginsUrl(
            $this->getBaseName() . $filePath
        );
    }

    private function getBaseName(): string
    {
        $baseName     = $this->wpService->pluginBasename(__FILE__);
        $explodedPath = explode('/', $baseName);

        return rtrim(
            array_shift($explodedPath),
            DIRECTORY_SEPARATOR
        ) . DIRECTORY_SEPARATOR;
    }
}
