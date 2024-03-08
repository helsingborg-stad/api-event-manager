<?php

namespace EventManager\TagReader;

class TagReader implements TagReaderInterface
{
    private string $input;
    private array $tags;

    public function getTags(string $input): array
    {
        $this->input = $input;
        $this->extractTags();
        $this->validateTags();
        $this->sanitizeTags();
        return $this->tags;
    }

    private function extractTags()
    {
        preg_match_all('/#[^\s#]+/', $this->input, $matches);
        $this->tags = $matches[0];
    }

    private function validateTags()
    {
        $invalidTags    = [];
        $invalidPattern = '/[^A-Za-z0-9åäö#]/'; // Not a letter, digit, underscore, or '#'.

        foreach ($this->tags as $tag) {
            if (preg_match($invalidPattern, $tag)) {
                $invalidTags[] = $tag;
            }
        }

        $this->tags = array_diff($this->tags, $invalidTags);
    }

    // Sanitize tags to contain only lowercase letters and digits.
    private function sanitizeTags()
    {
        $this->tags = array_map(function ($tag) {
            $tag = strtolower($tag);
            return strtolower(preg_replace('/[^a-z0-9åäö]/', '', $tag));
        }, $this->tags);
    }
}
