<?php

namespace EventManager\Taxonomies;

class Accessibility extends Taxonomy
{
    public function getName(): string
    {
        return 'accessibility';
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
                'manage_terms' => 'administrator',
                'edit_terms'   => 'administrator',
                'delete_terms' => 'administrator',
                'assign_terms' => 'assign_event_terms',
            ],
        );
    }

    public function getLabelSingular(): string
    {
        return $this->wpService->__('Accessibility', 'api-event-manager');
    }

    public function getLabelPlural(): string
    {
        return $this->wpService->__('Accessibilities', 'api-event-manager');
    }
}
