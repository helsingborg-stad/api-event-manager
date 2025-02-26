<?php

namespace EventManager\Taxonomies;

class Organization extends Taxonomy
{
    public function getName(): string
    {
        return 'organization';
    }

    public function getObjectType(): string
    {
        return 'event';
    }

    public function getArgs(): array
    {
        return array(
            'show_in_rest' => false,
            'public'       => true,
            'hierarchical' => true,
            'show_ui'      => true,
            'meta_box_cb'  => false,
            'show_in_rest' => true,
            'capabilities' => [
                'manage_terms' => 'manage_organizations',
                'edit_terms'   => 'edit_organizations',
                'delete_terms' => 'delete_organizations',
                'assign_terms' => 'assign_organizations',
            ],
        );
    }

    public function getLabelSingular(): string
    {
        return 'Organization';
    }

    public function getLabelPlural(): string
    {
        return 'Organizations';
    }
}
