<?php

namespace EventManager\PreGetPostModifiers;

use AcfService\Contracts\GetField;
use WP_Query;
use WP_User;
use WpService\Contracts\AddAction;
use WpService\Contracts\WpGetCurrentUser;
use WpService\Contracts\IsAdmin;

class LimitEventTableResultsByUserRole implements IPreGetPostModifier
{
    public function __construct(private WpGetCurrentUser&IsAdmin&AddAction $wpService, private GetField $acfService)
    {
    }

    public function addHooks(): void
    {
        $this->wpService->addAction('pre_get_posts', [$this, 'modify']);
    }

    public function modify(WP_Query $query): WP_Query
    {
        if ($this->shouldModify($query)) {
            // Get users organization
            $currentUser   = $this->wpService->wpGetCurrentUser();
            $organizations = $this->acfService->getField('organizations', 'user_' . $currentUser->ID);
            $query->set('tax_query', [
                [
                    'taxonomy' => 'organization',
                    'field'    => 'term_id',
                    'terms'    => $organizations
                ]
            ]);
        }

        return $query;
    }

    private function shouldModify(WP_Query $query): bool
    {
        return
            $this->wpService->isAdmin() &&
            $query->get('post_type') === 'event' &&
            $query->is_main_query() &&
            $this->wpService->wpGetCurrentUser()->has_cap('administrator') !== true;
    }
}
