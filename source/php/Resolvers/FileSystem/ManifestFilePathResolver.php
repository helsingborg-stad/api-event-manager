<?php 

namespace EventManager\Resolvers\FileSystem;

use EventManager\Services\FileSystem\FileExists;
use EventManager\Services\FileSystem\GetFileContent;
use EventManager\Services\WPService\WPServiceFactory;

class ManifestFilePathResolver implements FilePathResolverInterface
{
  public function __construct(
    private string $manifestFilePath, 
    private FileExists&GetFileContent $fileSystem,
    private ?FilePathResolverInterface $inner = new StrictFilePathResolver())
  {
    
  }

  public function resolve(string $filePath): string
  {
    if($this->fileSystem->fileExists($this->manifestFilePath)) {
      $manifestFileContent = $this->fileSystem->getFileContent($this->manifestFilePath);
      $manifest = json_decode($manifestFileContent, true);

      if(isset($manifest[$filePath])) {
        return $this->resolveToUrl($manifest[$filePath]);
      }
    }
    return $this->inner->resolve($filePath);
  }

  private function resolveToUrl(string $filePath): string
  {
    $wpService = WPServiceFactory::create();

    return $wpService->pluginsUrl(
      dirname($this->manifestFilePath) . "/" . $filePath
    );
  }
}
