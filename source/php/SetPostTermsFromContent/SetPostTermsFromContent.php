<?php

namespace EventManager\SetPostTermsFromContent;

use EventManager\HooksRegistrar\Hookable;
use EventManager\TagReader\TagReaderInterface;
use WpService\Contracts\AddAction;
use WpService\Contracts\ApplyFilters;
use WpService\Contracts\GetPost;
use WpService\Contracts\WpInsertTerm;
use WpService\Contracts\WpSetPostTerms;
use WpService\Contracts\TermExists;

class SetPostTermsFromContent implements Hookable
{
    public function __construct(
        private string $postType,
        private string $taxonomy,
        private TagReaderInterface $tagReader,
        private AddAction&GetPost&ApplyFilters&WpSetPostTerms&TermExists&WpInsertTerm $wpService
    ) {
        $this->tagReader = $tagReader;
        $this->wpService = $wpService;
        $this->postType  = $postType;
        $this->taxonomy  = $taxonomy;
    }

    public function addHooks(): void
    {
        $this->wpService->addAction('save_post', [$this, 'setPostTermsFromContent']);
    }

    public function setPostTermsFromContent(int $postId): void
    {
        $post = $this->wpService->getPost($postId);

        if ($post->post_type !== $this->postType) {
            return;
        }

        $filterTag = 'EventManager\SetPostTermsFromContent\PostContent';
        $content   = $this->wpService->applyFilters($filterTag, $postId, $post->post_content);
        $tagNames  = $this->tagReader->getTags($content);

        $this->ensureFoundTagsExist($tagNames);

        $this->wpService->wpSetPostTerms($postId, $tagNames, $this->taxonomy, false);
    }

    private function ensureFoundTagsExist(array $tagNames): void
    {
        foreach ($tagNames as $tagName) {
            // If the tag does not exist, create it
            if (!$this->wpService->termExists($tagName, $this->taxonomy)) {
                $this->wpService->wpInsertTerm($tagName, $this->taxonomy);
            }
        }
    }
}
