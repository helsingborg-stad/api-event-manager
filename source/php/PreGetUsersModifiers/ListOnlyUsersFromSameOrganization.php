<?php

namespace EventManager\PreGetUsersModifiers;

use AcfService\Contracts\GetField;
use WP_User;
use WP_User_Query;
use WpService\Contracts\WpGetCurrentUser;
use WpService\Contracts\IsAdmin;

class ListOnlyUsersFromSameOrganization implements IPreGetUsersModifier
{
    public function __construct(private WpGetCurrentUser&IsAdmin $wpService, private GetField $acfService)
    {
    }

    public function addHooks(): void
    {
        add_action('pre_get_users', [$this, 'modify']);
    }

    public function modify(WP_User_Query $query): WP_User_Query
    {
        if ($this->shouldModify($this->wpService->wpGetCurrentUser())) {
            $currentUser   = $this->wpService->wpGetCurrentUser();
            $organizations = $this->acfService->getField('organizations', 'user_' . $currentUser->ID) ?? [];
            $metaQueries   = array_map(fn($organization) => [
                'key'     => 'organizations',
                'value'   => '"' . $organization . '"',
                'compare' => 'LIKE'

            ], $organizations);
            $query->set('meta_query', $metaQueries);
        }

        return $query;
    }

    private function shouldModify(WP_User $user): bool
    {
        return
            $this->wpService->isAdmin() &&
            $user->has_cap('organization_administrator') === true;
    }
}
