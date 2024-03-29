<?php

namespace EventManager\CleanupUnusedTags;

use EventManager\Helper\Hookable;
use EventManager\Services\WPService\WPService;

class CleanupUnusedTags implements Hookable
{
    public function __construct(private string $taxonomy, private WPService $wpService)
    {
    }

    public function addHooks(): void
    {
        add_action('save_post', [$this, 'cleanupUnusedTags']);
    }

    public function cleanupUnusedTags(): void
    {
        $terms = $this->wpService->getTerms([$this->taxonomy, 'hide_empty' => false]);

        foreach ($terms as $term) {
            if ($term->count === 0) {
                $this->wpService->deleteTerm($term->term_id, $this->taxonomy);
            }
        }
    }
}
