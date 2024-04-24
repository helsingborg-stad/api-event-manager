<?php

namespace EventManager\ContentExpirationManagement;

use AcfService\Contracts\GetField;
use WpService\Contracts\GetPosts;

class ExpiredEvents implements GetExpiredPostsInterface
{
    private $postType = 'event';

    public function __construct(
        private int $expirationTimestamp,
        private GetPosts $wpService,
        private GetField $acfService
    ) {
    }

    public function getExpiredPosts(): array
    {
        $posts = $this->wpService->getPosts([
            'post_type'      => $this->postType,
            'fields'         => 'ids',
            'posts_per_page' => -1
        ]);

        return array_filter($posts, fn($postId) => $this->eventHasExpired($postId));
    }

    private function eventHasExpired(int $postId): bool
    {
        $occasions = $this->acfService->getField('occasions', $postId);

        if (empty($occasions) || !is_array($occasions)) {
            return false;
        }

        $dates = array_merge(
            array_column($occasions, 'date'),
            array_column($occasions, 'untilDate')
        );

        if (empty($dates)) {
            return false;
        }

        $latestDate = max($dates);

        return strtotime($latestDate) < $this->expirationTimestamp;
    }
}
