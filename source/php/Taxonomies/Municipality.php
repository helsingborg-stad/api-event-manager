<?php

namespace EventManager\Taxonomies;

use WpService\Contracts\__;

class Municipality extends Taxonomy
{
    public function getName(): string
    {
        return 'municipality';
    }

    public function getObjectType(): string
    {
        return 'event';
    }

    public function getArgs(): array
    {
        return array(
            'show_in_rest' => true,
            'public'       => true,
            'hierarchical' => false,
            'show_ui'      => true,
            'meta_box_cb'  => 'post_tags_meta_box',
            'capabilities' => [
                'manage_terms' => 'administrator',
                'edit_terms'   => 'administrator',
                'delete_terms' => 'administrator',
                'assign_terms' => 'assign_event_terms',
            ],
        );
    }

    public function getLabelSingular(): string
    {
        return $this->wpService->__('Municipality', 'api-event-manager');
    }

    public function getLabelPlural(): string
    {
        return $this->wpService->__('Municipalities', 'api-event-manager');
    }
}
