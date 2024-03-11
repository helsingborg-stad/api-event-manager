<?php

namespace EventManager\Taxonomies;

use EventManager\Helper\Taxonomy;

class Keyword extends Taxonomy
{
    public function getName(): string
    {
        return 'keyword';
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
        return 'Keyword';
    }

    public function getLabelPlural(): string
    {
        return 'Keywords';
    }
}
