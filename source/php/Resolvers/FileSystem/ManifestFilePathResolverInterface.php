<?php

  namespace EventManager\Resolvers\FileSystem;

  interface ManifestFilePathResolverInterface extends FilePathResolverInterface
  {
    public function resolveToUrl(string $filePath): string;
  }