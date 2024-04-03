<?php

  namespace EventManager\Resolvers\FileSystem;

  use EventManager\Services\WpService\WPServiceFactory;

  class UrlFilePathResolver implements FilePathResolverInterface
  {
    public $wpService;

    public function __construct()
    {
      $this->wpService = WPServiceFactory::create();
    }

    public function resolve(string $filePath): string
    {
      return $this->wpService->pluginsUrl(
        $this->getBaseName() . $filePath
      );
    }

    private function getBaseName(): string
    {
      return rtrim(
        array_shift(explode('/', $this->wpService->pluginBasename(__FILE__))),
        DIRECTORY_SEPARATOR
      ) . DIRECTORY_SEPARATOR;
    }
  }