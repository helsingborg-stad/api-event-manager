<?php

namespace EventManager\PostTableColumns\ColumnCellContent;

use WpService\Contracts\EscHtml;
use WpService\Contracts\GetPostMeta;
use WpService\Contracts\GetTheID;

class MetaStringCellContent implements ColumnCellContentInterface
{
    public function __construct(
        private string $metaKey,
        private GetTheID&GetPostMeta&EscHtml $wpService
    ) {
    }

    public function getCellContent(): string
    {
        $postId = $this->wpService->getTheID();
        $value  = $this->wpService->getPostMeta($postId, $this->metaKey, true);
        return $this->sanitizeValue($value);
    }

    private function sanitizeValue(mixed $value): string
    {
        if (!is_string($value) && !is_numeric($value)) {
            return '';
        }

        return $this->wpService->escHtml((string)$value);
    }
}
