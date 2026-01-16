<?php

namespace EventManager\CleanupUnusedTags;

use EventManager\HooksRegistrar\Hookable;
use WpService\Contracts\AddAction;
use WpService\Contracts\WpDeleteTerm;
use WpService\Contracts\GetTerms;

class CleanupUnusedTags implements Hookable
{
    public function __construct(private string $taxonomy, private GetTerms&WpDeleteTerm&AddAction $wpService)
    {
    }

    public function addHooks(): void
    {
        $this->wpService->addAction('save_post', [$this, 'cleanupUnusedTags']);
    }

    public function cleanupUnusedTags(): void
    {
        $terms = $this->wpService->getTerms(['taxonomy' => $this->taxonomy, 'hide_empty' => false]);

        foreach ($terms as $term) {
            if ($term->count === 0) {
                $this->wpService->wpDeleteTerm($term->term_id, $this->taxonomy);
            }
        }
    }
}
