<?php

namespace EventManager\TagReader;

class TagReader implements TagReaderInterface {

    private string $input;
    private array $tags;

    public function __construct(string $input)
    {
        $this->input = $input;
    }

    public function getTags(): array {
        $this->extractTags();
        $this->validateTags();
        $this->sanitizeTags();
        return $this->tags;
    }

    private function extractTags() {
        preg_match_all('/#[^\s#]+/', $this->input, $matches);
        $this->tags = $matches[0];

    }

    private function validateTags() {
        $invalidTags = [];
        $invalidPattern = '/[^A-Za-z0-9#]/'; // Not a letter, digit, underscore, or '#'.
        
        foreach ($this->tags as $tag) {
            if (preg_match($invalidPattern, $tag)) {
                $invalidTags[] = $tag;
            }
        }
        
        if (!empty($invalidTags)) {
            $notice = 'Tags with special characters found: ' . implode(', ', $invalidTags);
            trigger_error($notice, E_USER_NOTICE);
            
        }
        
        $this->tags = array_diff($this->tags, $invalidTags);
    }

    // Sanitize tags to contain only lowercase letters and digits.
    private function sanitizeTags() {
        $this->tags = array_map(function($tag) {
            return strtolower(preg_replace('/[^A-Za-z0-9]/', '', $tag));
        }, $this->tags);
    }
}