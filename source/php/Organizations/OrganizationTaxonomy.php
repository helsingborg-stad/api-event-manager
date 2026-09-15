<?php

namespace EventManager\Organizations;

use EventManager\Taxonomies\Taxonomy;
use WpService\Contracts\__;
use WpService\Contracts\AddAction;
use WpService\Contracts\RegisterTaxonomy;

class OrganizationTaxonomy extends Taxonomy
{
    public function __construct(protected AddAction&RegisterTaxonomy&__ $wpService, private string $taxonomy)
    {
        parent::__construct($wpService);
    }

    public function addHooks(): void
    {
        parent::addHooks();
    }

    public function getName(): string
    {
        return $this->taxonomy;
    }

    public function getObjectType(): string
    {
        return 'event';
    }

    public function getArgs(): array
    {
        return array(
            'public'       => true,
            'hierarchical' => true,
            'show_ui'      => true,
            'meta_box_cb'  => false,
            'show_in_rest' => true,
            'capabilities' => [
                'manage_terms' => 'manage_' . $this->taxonomy,
                'edit_terms'   => 'edit_' . $this->taxonomy,
                'delete_terms' => 'delete_' . $this->taxonomy,
                'assign_terms' => 'assign_' . $this->taxonomy,
            ],
        );
    }

    public function getLabelSingular(): string
    {
        return $this->wpService->__('Organization', 'api-event-manager');
    }

    public function getLabelPlural(): string
    {
        return $this->wpService->__('Organizations', 'api-event-manager');
    }
}
