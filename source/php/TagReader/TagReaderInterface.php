<?php

namespace EventManager\TagReader;

/**
 * The TagReaderInterface defines the contract for classes that can read tags from input.
 */
interface TagReaderInterface
{
    /**
     * Retrieves an array of tags from the given input.
     *
     * @param string $input The input string to read tags from.
     * @return array An array of tags.
     */
    public function getTags(string $input): array;
}
