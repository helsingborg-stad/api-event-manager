<?php 

if (function_exists('acf_add_local_field_group')) {
    acf_add_local_field_group(array(
    'key' => 'group_589dd0fbd412e',
    'title' => __('Guide Group', 'event-manager'),
    'fields' => array(
        0 => array(
            'key' => 'field_589dd138aca7e',
            'label' => __('Select the group for this guide', 'event-manager'),
            'name' => 'guidegroup',
            'type' => 'taxonomy',
            'instructions' => __('This field is mandatory for v1 of the app. This will not be required to be set after January 1:st 2018.', 'event-manager'),
            'required' => 0,
            'conditional_logic' => 0,
            'wrapper' => array(
                'width' => '',
                'class' => '',
                'id' => '',
            ),
            'taxonomy' => 'guidegroup',
            'field_type' => 'select',
            'allow_null' => 0,
            'add_term' => 1,
            'save_terms' => 0,
            'load_terms' => 0,
            'return_format' => 'id',
            'multiple' => 0,
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