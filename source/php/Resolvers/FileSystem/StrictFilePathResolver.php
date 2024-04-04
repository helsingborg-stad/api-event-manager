<?php

  namespace EventManager\Resolvers\FileSystem;

  class StrictFilePathResolver implements FilePathResolverInterface
  {
    public function resolve(string $filePath): string
    {
      throw new \Exception('StrictFilePathResolver:: could not resolve: ' . $filePath );
      return "";
    }
  }