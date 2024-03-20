<?php 

namespace EventManager\Resolvers\FileSystem;

use EventManager\Services\FileSystem\FileExists;
use EventManager\Services\FileSystem\GetFileContent;

class ManifestFilePathResolver implements FilePathResolverInterface
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
}

