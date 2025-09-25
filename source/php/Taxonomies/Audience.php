<?php

namespace EventManager\Taxonomies;

class Audience extends Taxonomy
{
    public function getName(): string
    {
        return 'audience';
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
            'hierarchical' => false,
            'show_ui'      => true,
            'meta_box_cb'  => false,
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
        return $this->wpService->__('Audience', 'api-event-manager');
    }

    public function getLabelPlural(): string
    {
        return $this->wpService->__('Audiences', 'api-event-manager');
    }
}
