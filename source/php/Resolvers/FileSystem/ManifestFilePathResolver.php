<?php

namespace EventManager\Resolvers\FileSystem;

use EventManager\Services\FileSystem\FileExists;
use EventManager\Services\FileSystem\GetFileContent;
use EventManager\Services\WPService\WPServiceFactory;

class ManifestFilePathResolver implements FilePathResolverInterface
{
    private $wpService;

    public function __construct(
        private string $manifestFilePath,
        private FileExists&GetFileContent $fileSystem,
        private ?FilePathResolverInterface $inner = new StrictFilePathResolver()
    ) {
        $this->wpService = WPServiceFactory::create();
    }

    public function resolve(string $filePath): string
    {
        if ($this->fileSystem->fileExists($this->manifestFilePath)) {
            $manifestPath = $this->getManifestPath($this->manifestFilePath);
            $basePath     = $this->getBasePath();
            $pathDiff     = $this->pathDiff($manifestPath, $basePath);

            $manifestFileContent = $this->fileSystem->getFileContent($this->manifestFilePath);
            $manifest            = json_decode($manifestFileContent, true);

            if ($manifest === null) {
                throw new \Exception('Manifest file (' . $this->manifestFilePath . ') is not a valid JSON: ' . json_last_error_msg());
            }

            if (isset($manifest[$filePath])) {
                $filePath = $pathDiff . DIRECTORY_SEPARATOR . $manifest[$filePath];
            }
        }

        return $this->inner->resolve($filePath);
    }

    private function pathDiff(string $manifestFilePath, string $basePath): string
    {
        $manifestFilePath = explode('/', $manifestFilePath);
        $basePath         = explode('/', $basePath);
        $diff             = array_diff($manifestFilePath, $basePath);
        return implode('/', $diff);
    }

    private function getBasePath(): string
    {
        return $this->wpService->pluginDirPath(__FILE__);
    }

    private function getManifestPath(): string
    {
        return dirname($this->manifestFilePath);
    }
}
