<?php

namespace EventManager\Notifications\MarkdownParser;

interface MarkdownParserInterface
{
    /**
     * Parses the given markdown string and returns the resulting HTML.
     *
     * @param string $markdown The markdown string to parse.
     * @return string The resulting HTML after parsing the markdown.
     */
    public function parse(string $markdown): string;
}
