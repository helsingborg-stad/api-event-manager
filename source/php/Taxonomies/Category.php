<?php

namespace EventManager\Taxonomies;

class Category extends Taxonomy
{
    public function getName(): string
    {
        return 'category';
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
            'meta_box_cb'  => false
        );
    }

    public function getLabelSingular(): string
    {
        return 'Category';
    }

    public function getLabelPlural(): string
    {
        return 'Categories';
    }
}
