<?php

namespace EventManager\Notifications\MarkdownParser;

class MarkdownParser implements MarkdownParserInterface
{
    public function parse(string $markdown): string
    {
        return (new \Parsedown())->text($markdown);
    }
}
