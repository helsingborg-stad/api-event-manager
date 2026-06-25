<?php

namespace EventManager\Organizations;

use EventManager\HooksRegistrar\Hookable;
use WpService\Contracts\__;
use WpService\Contracts\AddFilter;
use WpService\Contracts\GetTerm;
use WpService\Contracts\GetUserdata;
use WpService\Contracts\GetUserMeta;

class UserTableOrganizationColumn implements Hookable
{
    public function __construct(private AddFilter&__&GetUserdata&GetUserMeta&GetTerm $wpService, private string $taxonomy)
    {
    }

    public function addHooks(): void
    {
        $this->wpService->addFilter('manage_users_columns', [$this, 'addOrganizationColumn']);
        $this->wpService->addFilter('manage_users_custom_column', [$this, 'populateOrganizationColumn'], 10, 3);
    }

    public function addOrganizationColumn(array $columns): array
    {
        $columns['organization'] = $this->wpService->__('Organization', 'api-event-manager');
        return $columns;
    }

    public function populateOrganizationColumn(string $content, string $column_name, int $userId): string
    {
        if ($column_name === 'organization') {
            return (string) $this->getOrganizationFromUser($userId);
        }

        return $content;
    }

    private function getOrganizationFromUser(int $userId): string
    {
        $user = $this->wpService->getUserdata($userId);
        if (!$user) {
            return '';
        }

        $organizationIds = $this->wpService->getUserMeta($userId, 'organizations', true);
        if (empty($organizationIds)) {
            return '';
        }

        $organizationNames = [];
        foreach ((array) $organizationIds as $organizationId) {
            $term = $this->wpService->getTerm($organizationId, $this->taxonomy);
            if ($term && !$term instanceof \WP_Error) {
                $organizationNames[] = $term->name;
            }
        }

        return implode(', ', $organizationNames);
    }
}
