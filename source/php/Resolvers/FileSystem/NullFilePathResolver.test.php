<?php

use EventManager\Resolvers\FileSystem\NullFilePathResolver;
use PHPUnit\Framework\TestCase;

class NullFilePathResolverTest extends TestCase
{
    public function testDecorateReturnsSameFilePath()
    {
        $decorator = new NullFilePathResolver();
        $filePath = '/path/to/file.txt';

        $decoratedFilePath = $decorator->resolve($filePath);

        $this->assertEquals($filePath, $decoratedFilePath);
    }
}