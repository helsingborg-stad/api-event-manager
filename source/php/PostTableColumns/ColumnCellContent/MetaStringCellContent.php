<?php

namespace EventManager\PostTableColumns\ColumnCellContent;

use EventManager\Services\WPService\GetPostMeta;
use EventManager\Services\WPService\GetTheId;

class MetaStringCellContent implements ColumnCellContentInterface
{
    public function __construct(
        private string $metaKey,
        private GetTheId&GetPostMeta $wpService
    ) {
    }

    public function getCellContent(): string
    {
        $postId = $this->wpService->getTheId();
        $value  = $this->wpService->getPostMeta($postId, $this->metaKey, true);
        return $this->sanitizeValue($value);
    }

    private function sanitizeValue(mixed $value): string
    {
        if (!is_string($value) && !is_numeric($value)) {
            return '';
        }

        return (string) $value;
    }
}
