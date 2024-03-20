<?php

  namespace EventManager\Resolvers\FileSystem;

  class StrictFilePathResolver implements FilePathResolverInterface
  {
    public function resolve(string $filePath): string
    {
      die('StrictFilePathResolver:: could not resolve: ' . $filePath ); 
      return $filePath;
    }
  }