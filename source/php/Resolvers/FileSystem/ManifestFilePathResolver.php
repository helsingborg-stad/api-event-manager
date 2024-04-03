<?php 

namespace EventManager\Resolvers\FileSystem;

use EventManager\Services\FileSystem\FileExists;
use EventManager\Services\FileSystem\GetFileContent;

class ManifestFilePathResolver implements ManifestFilePathResolverInterface
{

  public function __construct(
    private string $manifestFilePath, 
    private FileExists&GetFileContent $fileSystem,
    private ?FilePathResolverInterface $inner = new NullFilePathResolver())
  {
  }

  public function resolve(string $filePath): string
  {
    if($this->fileSystem->fileExists($this->manifestFilePath)) {
      $manifestFileContent = $this->fileSystem->getFileContent($this->manifestFilePath);
      $manifest = json_decode($manifestFileContent, true);

      if(isset($manifest[$filePath])) {
        return $manifest[$filePath];
      }
    }
    return $this->inner->resolve($filePath);
  }

  public function resolveToUrl(string $filePath): string
  {

    //Make out the additional path to the manifest file
    $additionalPath = str_replace(
      plugin_dir_path(dirname($this->manifestFilePath)), // TODO: Create a wpService for plugin_dir_path
      '', 
      dirname($this->manifestFilePath)
    ) . "/";

    return plugins_url( //TODO: Create a wpService for plugins_url
      $additionalPath . $this->resolve($filePath), 
      dirname($this->manifestFilePath)
    );
  }
}

