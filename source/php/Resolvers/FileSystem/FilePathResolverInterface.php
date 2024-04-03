<?php

  namespace EventManager\Resolvers\FileSystem;

  interface FilePathResolverInterface
  {
    public function resolve(string $filePath): string;
  }