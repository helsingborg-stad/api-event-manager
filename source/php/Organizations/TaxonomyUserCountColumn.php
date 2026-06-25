<?php

namespace EventManager\Organizations;

use EventManager\HooksRegistrar\Hookable;
use WpService\Contracts\__;
use WpService\Contracts\AdminUrl;
use WpService\Contracts\EscUrl;

class TaxonomyUserCountColumn implements Hookable
{
    public function __construct(private \WpService\Contracts\AddFilter&__&AdminUrl&EscUrl $wpService, private string $taxonomy)
    {
    }

    public function addHooks(): void
    {
        $this->wpService->addFilter('manage_edit-' . $this->taxonomy . '_columns', [$this, 'addUserCountColumn']);
        $this->wpService->addFilter('manage_' . $this->taxonomy . '_custom_column', [$this, 'populateUserCountColumn'], 10, 3);
    }

    public function addUserCountColumn(array $columns): array
    {
        $columns['user_count'] = $this->wpService->__('User count', 'api-event-manager');
        return $columns;
    }

    public function populateUserCountColumn(string $content, string $column_name, int $term_id): string
    {
        if ($column_name === 'user_count') {
            return (string) $this->getColumnOutput($term_id);
        }

        return $content;
    }

    private function getColumnOutput(int $term_id): string
    {
        $count = $this->getUserCountForTerm($term_id);

        if ($count === 0) {
            return '0';
        }

        return '<a href="' . $this->getUserTableHref($term_id) . '">' . $count . '</a>';
    }

    private function getUserTableHref(int $termId): string
    {
        $href = $this->wpService->adminUrl('users.php?organization=' . $termId);

        return $this->wpService->escUrl($href);
    }

    private function getUserCountForTerm(int $termId): int
    {
        $query = new \WP_User_Query([
            'meta_query' => [
                [
                    'key'     => 'organizations',
                    'value'   => '"' . $termId . '"',
                    'compare' => 'LIKE',
                ],
            ],
        ]);

        return $query->get_total();
    }
}
