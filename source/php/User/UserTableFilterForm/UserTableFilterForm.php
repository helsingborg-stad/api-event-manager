<?php

namespace EventManager\User\UserTableFilterForm;

use EventManager\HooksRegistrar\Hookable;
use WpService\Contracts\__;
use WpService\Contracts\AddAction;
use WpService\Contracts\DoAction;
use WpService\Contracts\SubmitButton;

class UserTableFilterForm implements Hookable
{
    public function __construct(private AddAction&__&SubmitButton&DoAction $wpService)
    {
    }

    public function addHooks(): void
    {
        $this->wpService->addAction('manage_users_extra_tablenav', [$this, 'outputOrganizationFilterDropdown'], 10, 1);
    }

    public function outputOrganizationFilterDropdown(string $which): void
    {
        global $wp_list_table;

        if (count($wp_list_table->items) > 0 && 'top' === $which) {
            echo '<div class="alignleft actions">';
            $this->wpService->doAction('EventManager\UserTableFilterForm');
            $this->wpService->submitButton($this->wpService->__('Filter'), 'secondary', true, false);
            echo '</div>';
        }
    }
}
