<?php 

namespace EventManager\Decorators;


class ManifestFilePathDecorator implements FilePathDecoratorInterface
{
  private $manifestFilename   = 'manifest.json';
  private $assetDirectory     = 'dist';

  public function decorate(string $filePath): string
  {
    $wp_filesystem  = $this->initFilesystem();
    $manifestPath   = EVENT_MANAGER_PATH . $this->assetDirectory . '/' . $this->manifestFilename;

    if($wp_filesystem->exists($manifestPath)) {
      $manifest = json_decode(
        $wp_filesystem->get_contents($manifestPath), 
        true
      );

      $filePath = $manifest[$filePath] ?? $filePath;
    }

    return EVENT_MANAGER_URL . "/" . $this->assetDirectory . "/" . $filePath;
  }

  private function initFilesystem(): \WP_Filesystem_Base|\WP_Filesystem_Direct|\WP_Filesystem_FTPext|\WP_Filesystem_ftpsocket|\WP_Filesystem_SSH2
  {
    global $wp_filesystem;
    require_once(ABSPATH . '/wp-admin/includes/file.php');
    WP_Filesystem();
    return $wp_filesystem;
  }
}

