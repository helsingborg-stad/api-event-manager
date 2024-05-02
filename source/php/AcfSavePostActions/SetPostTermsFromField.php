<?php

namespace EventManager\AcfSavePostActions;

use AcfService\Contracts\GetField;
use WpService\Contracts\SetPostTerms;

class SetPostTermsFromField implements IAcfSavePostAction
{
    public function __construct(
        private string $fieldName,
        private string $taxonomy,
        private SetPostTerms $wpService,
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

        $this->wpService->setPostTerms($postId, $terms, $this->taxonomy);
    }

    private function sanitizeTerms(mixed $terms): array
    {
        if (!is_array($terms)) {
            $terms = [$terms];
        }

        return array_filter(array_map('intval', $terms));
    }
}
