<?php

namespace EventManager\TableColumns\PostTableColumns;

use EventManager\Services\WPService\GetPostTerms;
use EventManager\Services\WPService\GetTheId;
use EventManager\Services\WPService\IsWPError;
use EventManager\TableColumns\TableColumnInterface;

class TermNameTableColumn implements TableColumnInterface
{
    public function __construct(
        private string $header,
        private string $taxonomy,
        private GetTheId&GetPostTerms&IsWPError $wpService
    ) {
    }

    public function getHeader(): string
    {
        return $this->header;
    }

    public function getName(): string
    {
        return $this->taxonomy;
    }

    public function getCellContent(): string
    {
        $postId = $this->wpService->getTheId();
        $terms  = $this->wpService->getPostTerms($postId, $this->taxonomy);

        if ($this->wpService->isWPError($terms) || empty($terms)) {
            return '';
        }

        return $terms[0]->name;
    }
}
