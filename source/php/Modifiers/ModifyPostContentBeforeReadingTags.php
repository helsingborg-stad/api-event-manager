<?php

namespace EventManager\Modifiers;

use EventManager\Helper\Hookable;
use EventManager\Services\WPService\WPService;

class ModifyPostContentBeforeReadingTags implements Hookable
{
    public function __construct(private WPService $wpService)
    {
    }

    public function addHooks(): void
    {
        $filter = 'EventManager\SetPostTermsFromContent\PostContent';
        add_filter($filter, [$this, 'modifyPostContentBeforeReadingTags'], 10, 2);
    }

    public function modifyPostContentBeforeReadingTags(int $postId, string $content): string
    {
        return $this->wpService->getPostMeta($postId, 'about', true) ?? '';
    }
}
