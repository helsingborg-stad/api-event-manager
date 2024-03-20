<?php

namespace EventManager\Services\FileSystem;

interface FileExists {
    public function fileExists(string $path): bool;
}