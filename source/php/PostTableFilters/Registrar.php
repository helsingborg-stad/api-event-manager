<?php

namespace EventManager\PostTableFilters;

use EventManager\HooksRegistrar\Hookable;
use WpService\Contracts\AddAction;

class Registrar implements Hookable
{
    /**
     * @param IPostTableFilter[] $filters
     * @param AddAction $wpService
     */
    public function __construct(private array $filters, private AddAction $wpService)
    {
    }

    public function addHooks(): void
    {
        foreach ($this->filters as $filter) {
            $this->wpService->addAction('restrict_manage_posts', [$filter, 'outputFilterMarkup']);
        }
    }
}
