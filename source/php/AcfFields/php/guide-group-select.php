<?php

if (function_exists('acf_add_local_field_group')) {
    acf_add_local_field_group(array(
    'key' => 'group_589dd0fbd412e',
    'title' => __('Guide Group', 'event-manager'),
    'fields' => array(
        0 => array(
            'taxonomy' => 'guidegroup',
            'field_type' => 'select',
            'multiple' => 0,
            'allow_null' => 0,
            'return_format' => 'id',
            'add_term' => 1,
            'load_terms' => 0,
            'save_terms' => 0,
            'key' => 'field_589dd138aca7e',
            'label' => __('Select the group for this guide', 'event-manager'),
            'name' => 'guidegroup',
            'type' => 'taxonomy',
            'instructions' => '',
            'required' => 1,
            'conditional_logic' => 0,
            'wrapper' => array(
                'width' => '',
                'class' => '',
                'id' => '',
            ),
        ),
    ),
    'location' => array(
        0 => array(
            0 => array(
                'param' => 'post_type',
                'operator' => '==',
                'value' => 'guide',
            ),
        ),
    ),
    'menu_order' => 0,
    'position' => 'side',
    'style' => 'default',
    'label_placement' => 'top',
    'instruction_placement' => 'label',
    'hide_on_screen' => '',
    'active' => 1,
    'description' => '',
));
}
