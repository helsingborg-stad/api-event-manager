<?php

namespace EventManager\TagReader;

interface TagReaderInterface
{
    /**
     * @return string[]
     */
    public function getTags(): array;
}
