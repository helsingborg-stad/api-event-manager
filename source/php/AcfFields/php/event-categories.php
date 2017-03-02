<?php 

if (function_exists('acf_add_local_field_group')) {
    acf_add_local_field_group(array(
    'key' => 'group_5889f976dfb2d',
    'title' => 'Event categories',
    'fields' => array(
        0 => array(
            'key' => 'field_5889fa31bc5bf',
            'label' => __('Include imported categories', 'event-manager'),
            'name' => 'event_categories_map',
            'type' => 'taxonomy',
            'instructions' => __('Select which imported categories that will be included in this category.', 'event-manager'),
            'required' => 0,
            'conditional_logic' => 0,
            'wrapper' => array(
                'width' => '',
                'class' => '',
                'id' => '',
            ),
            'taxonomy' => 'imported_categories',
            'field_type' => 'checkbox',
            'allow_null' => 1,
            'add_term' => 0,
            'save_terms' => 0,
            'load_terms' => 0,
            'return_format' => 'object',
            'multiple' => 0,
        ),
    ),
    'location' => array(
        0 => array(
            0 => array(
                'param' => 'taxonomy',
                'operator' => '==',
                'value' => 'event_categories',
            ),
        ),
    ),
    'menu_order' => 0,
    'position' => 'normal',
    'style' => 'default',
    'label_placement' => 'top',
    'instruction_placement' => 'label',
    'hide_on_screen' => '',
    'active' => 1,
    'description' => '',
    'local' => 'json',
));
}