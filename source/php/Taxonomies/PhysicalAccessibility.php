<?php

namespace EventManager\Taxonomies;

class PhysicalAccessibility extends Taxonomy
{
    public function getName(): string
    {
        return 'physical-accessibility';
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
        );
    }

    public function getLabelSingular(): string
    {
        return 'Physical Accessibility';
    }

    public function getLabelPlural(): string
    {
        return 'Physical Accessibilities';
    }
}
