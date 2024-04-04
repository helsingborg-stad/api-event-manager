<?php

namespace EventManager\Services\WPService\Implementations;

use EventManager\Resolvers\FileSystem\FilePathResolverInterface;
use EventManager\Services\WPService\WPService;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;

class FilePathResolvingWpServiceTest extends TestCase
{
    /**
     * @testdox registerScript() calls the inner service with the resolved file path
     */
    public function testRegisterScript()
    {
        $innerService = $this->getInnerService();
        $innerService->expects($this->once())
            ->method('registerScript')
            ->with('handle', 'resolved: path/to/script.js');

        $filePathResolver = $this->getFilePathResolver();

        $filePathResolvingWpService = new FilePathResolvingWpService($innerService, $filePathResolver);
        $filePathResolvingWpService->registerScript('handle', 'path/to/script.js');
    }

    /**
     * @testdox registerStyle() calls the inner service with the resolved file path
     */
    public function testRegisterStyle()
    {
        $innerService = $this->getInnerService();
        $innerService->expects($this->once())
            ->method('registerStyle')
            ->with('handle', 'resolved: path/to/stylesheet.css');

        $filePathResolver = $this->getFilePathResolver();

        $filePathResolvingWpService = new FilePathResolvingWpService($innerService, $filePathResolver);
        $filePathResolvingWpService->registerStyle('handle', 'path/to/stylesheet.css');
    }

    private function getInnerService(): WPService|MockObject
    {
        return $this->createMock(WPService::class);
    }

    private function getFilePathResolver(): FilePathResolverInterface
    {
        return new class implements FilePathResolverInterface {
            public function resolve(string $path): string
            {
                return "resolved: {$path}";
            }
        };
    }
}
