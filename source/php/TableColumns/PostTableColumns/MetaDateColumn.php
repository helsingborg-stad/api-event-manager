<?php

namespace EventManager\TableColumns\PostTableColumns;

use EventManager\Services\WPService\GetPostMeta;
use EventManager\Services\WPService\GetTheId;
use EventManager\TableColumns\TableColumnInterface;

class OpenStreetMapTableColumn implements TableColumnInterface
{
    public function __construct(
        private string $header,
        private string $metaKey,
        private GetPostMeta&GetTheId $wpService
    ) {
    }

    public function getHeader(): string
    {
        return $this->header;
    }

    public function getName(): string
    {
        return $this->metaKey;
    }

    public function getCellContent(): string
    {
        $postId    = $this->wpService->getTheId();
        $metaValue = $this->wpService->getPostMeta($postId, $this->metaKey, true);

        if (!is_array($metaValue) || !isset($metaValue['address'])) {
            return '';
        }

        return $metaValue['address'];
    }
}
