<?php

  namespace EventManager\Decorators;

  interface FilePathDecoratorInterface
  {
    public function decorate(string $filePath): string;
  }