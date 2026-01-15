<?php

namespace EventManager\User;

use EventManager\HooksRegistrar\Hookable;
use WpService\Contracts\AddFilter;

class OverrideMunicipioUserAdminRestriction implements Hookable {
    public function __construct(private AddFilter $wpService)
    {
    }

    public function addHooks(): void
    {
        $this->wpService->addFilter('Municipio/Admin/Roles/General/AllowAccess', fn () => true);
    }
}