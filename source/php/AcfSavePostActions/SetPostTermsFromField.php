<?php

namespace EventManager\AcfSavePostActions;

use AcfService\Contracts\GetField;
use WpService\Contracts\WpSetPostTerms;

class SetPostTermsFromField implements IAcfSavePostAction
{
    public function __construct(
        private string $fieldName,
        private string $taxonomy,
        private WpSetPostTerms $wpService,
        private GetField $acfService
    ) {
    }

    public function savePost(int|string $postId): void
    {
        if (!is_int($postId)) {
            return;
        }

        $terms = $this->acfService->getField($this->fieldName, $postId);
        $terms = $this->sanitizeTerms($terms);

        $this->wpService->wpSetPostTerms($postId, $terms, $this->taxonomy);
    }

    private function sanitizeTerms(mixed $terms): array
    {
        if (!is_array($terms)) {
            $terms = [$terms];
        }

        return array_filter(array_map('intval', $terms));
    }
}
