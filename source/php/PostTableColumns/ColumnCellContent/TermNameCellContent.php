<?php

namespace EventManager\PostTableColumns\ColumnCellContent;

use WpService\Contracts\GetEditTermLink;
use WpService\Contracts\WpGetPostTerms;
use WpService\Contracts\GetTheID;
use WP_Error;
use WP_Term;

class TermNameCellContent implements ColumnCellContentInterface
{
    public function __construct(private string $taxonomy, private GetTheID&WpGetPostTerms&GetEditTermLink $wpService)
    {
    }

    public function getCellContent(): string
    {
        $postId = $this->wpService->getTheID();
        $terms  = $this->wpService->wpGetPostTerms($postId, $this->taxonomy);
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
