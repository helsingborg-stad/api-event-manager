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
            'meta_box_cb'  => false
        );
    }

    public function getLabelSingular(): string
    {
        return 'Audience';
    }

    public function getLabelPlural(): string
    {
        return 'Audiences';
    }
}
