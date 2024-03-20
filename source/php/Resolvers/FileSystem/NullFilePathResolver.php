<?php

  namespace EventManager\Resolvers\FileSystem;

  class NullFilePathResolver implements FilePathResolverInterface
  {
    public function resolve(string $filePath): string
    {
      return $filePath;
    }
  }