<?php

namespace EventManager\Services\WPService\Implementations;

use EventManager\Resolvers\FileSystem\FilePathResolverInterface;
use EventManager\Services\WPService\WPService;

class FilePathResolvingWpService extends WpServiceDecorator
{
    public function __construct(private WPService $inner, private FilePathResolverInterface $filePathResolver)
    {
        parent::__construct($inner);
    }

    public function registerScript(
        string $handle,
        string $src = '',
        array $deps = array(),
        string|bool|null $ver = false,
        bool $in_footer = true
    ): void {
        $src = $this->filePathResolver->resolve($src);
        $this->inner->{__FUNCTION__}(...func_get_args());
    }

    public function registerStyle(
        string $handle,
        string $src = '',
        array $deps = array(),
        string|bool|null $ver = false,
        string $media = 'all'
    ): void {
        $src = $this->filePathResolver->resolve($src);
        $this->inner->{__FUNCTION__}(...func_get_args());
    }
}
