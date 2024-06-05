<?php

namespace EventManager\Taxonomies;

class Amenities extends Taxonomy
{
    public function getName(): string
    {
        return 'amenities';
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
            'meta_box_cb'  => false
        );
    }

    public function getLabelSingular(): string
    {
        return 'Amenity';
    }

    public function getLabelPlural(): string
    {
        return 'Amenities';
    }
}
