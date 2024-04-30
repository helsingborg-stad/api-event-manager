<?php

namespace EventManager\Modifiers;

use EventManager\HooksRegistrar\Hookable;
use WpService\Contracts\AddFilter;
use WpService\Contracts\GetPostMeta;

class ModifyPostContentBeforeReadingTags implements Hookable
{
    public function __construct(private GetPostMeta&AddFilter $wpService)
    {
    }

    public function addHooks(): void
    {
        $filter = 'EventManager\SetPostTermsFromContent\PostContent';
        $this->wpService->addFilter($filter, [$this, 'modifyPostContentBeforeReadingTags'], 10, 2);
    }

    public function modifyPostContentBeforeReadingTags(int $postId, string $content): string
    {
        return $this->wpService->getPostMeta($postId, 'about', true) ?? '';
    }
}
