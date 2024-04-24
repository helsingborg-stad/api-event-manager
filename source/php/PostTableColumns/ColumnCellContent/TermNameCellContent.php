<?php

namespace EventManager\PostTableColumns\ColumnCellContent;

use WpService\Contracts\GetEditTermLink;
use WpService\Contracts\GetPostTerms;
use WpService\Contracts\GetTheId;
use WP_Error;
use WP_Term;

class TermNameCellContent implements ColumnCellContentInterface
{
    public function __construct(private string $taxonomy, private GetTheId&GetPostTerms&GetEditTermLink $wpService)
    {
    }

    public function getCellContent(): string
    {
        $postId = $this->wpService->getTheId();
        $terms  = $this->wpService->getPostTerms($postId, $this->taxonomy);
        return $this->formatOutput($terms);
    }

    /**
     * @param WP_Term[]|WP_Error $terms
     */
    private function formatOutput(array|WP_Error $terms): string
    {
        if ($terms instanceof WP_Error || empty($terms)) {
            return '';
        }

        $termLinks = array_map([$this, 'termToTermLink'], $terms);

        return join(', ', $termLinks);
    }

    private function termToTermLink(WP_Term $term): string
    {
        $editUrl = $this->wpService->getEditTermLink($term, $term->taxonomy);
        return "<a href=\"{$editUrl}\">{$term->name}</a>";
    }
}
