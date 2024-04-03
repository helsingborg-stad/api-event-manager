<?php

namespace EventManager\Services\FileSystem;

interface GetFileContent {
    public function getFileContent(string $path): string;
}