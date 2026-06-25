<?php

namespace EventManager\Organizations;

use EventManager\HooksRegistrar\Hookable;
use WpService\Contracts\__;

class TaxonomyUserCountColumn implements Hookable
{
    public function __construct(private \WpService\Contracts\AddFilter&__ $wpService, private string $taxonomy)
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
            return (string) $this->getUserCountForTerm($term_id);
        }

        return $content;
    }

    private function getUserCountForTerm(int $termId): int
    {
        // a:1:{i:0;s:3:"151";}
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
