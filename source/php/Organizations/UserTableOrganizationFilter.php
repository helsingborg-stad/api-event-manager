<?php

namespace EventManager\Organizations;

use EventManager\HooksRegistrar\Hookable;
use WpService\Contracts\__;
use WpService\Contracts\AddAction;
use WpService\Contracts\AddFilter;
use WpService\Contracts\EscAttr;
use WpService\Contracts\EscHtml;
use WpService\Contracts\EscHtml__;
use WpService\Contracts\GetTerm;
use WpService\Contracts\GetTerms;
use WpService\Contracts\GetUserdata;
use WpService\Contracts\GetUserMeta;
use WpService\Contracts\SubmitButton;

class UserTableOrganizationFilter implements Hookable
{
    public function __construct(private AddFilter&AddAction&__&GetUserdata&GetUserMeta&GetTerm&SubmitButton&EscHtml__&EscAttr&EscHtml&GetTerms $wpService, private string $taxonomy)
    {
    }

    public function addHooks(): void
    {
        $this->wpService->addFilter('pre_get_users', [$this, 'filterUsersByOrganization']);
        $this->wpService->addAction('EventManager\UserTableFilterForm', [$this, 'addOrganizationFilterDropdown'], 10, 1);
    }

    public function filterUsersByOrganization(\WP_User_Query $query): void
    {
        $organizationId = isset($_GET['organization']) ? (int) $_GET['organization'] : 0;

        if ($organizationId > 0) {
            $metaQuery   = $query->get('meta_query');
            $metaQuery[] = [
                'key'     => 'organizations',
                'value'   => '"' . $organizationId . '"',
                'compare' => 'LIKE',
            ];
            $query->set('meta_query', $metaQuery);
        }
    }

    public function addOrganizationFilterDropdown(): void
    {
        $taxonomy = $this->taxonomy;
        $terms    = $this->wpService->getTerms([
            'taxonomy'   => $taxonomy,
            'hide_empty' => false,
        ]);

        if (!empty($terms) && !$terms instanceof \WP_Error) {
            echo '<select name="organization" id="organization">';
            echo '<option value="">' . $this->wpService->escHtml__('All Organizations', 'api-event-manager') . '</option>';
            foreach ($terms as $term) {
                $selected = (isset($_GET['organization']) && (int) $_GET['organization'] === (int) $term->term_id) ? ' selected="selected"' : '';
                echo '<option value="' . $this->wpService->escAttr($term->term_id) . '"' . $selected . '>' . $this->wpService->escHtml($term->name) . '</option>';
            }
            echo '</select>';
        }
    }
}
