<?php 

if (function_exists('acf_add_local_field_group')) {
    acf_add_local_field_group(array(
    'key' => 'group_5885f51260b61',
    'title' => __('Groups', 'event-manager'),
    'fields' => array(
        0 => array(
            'key' => 'field_5885f58c36f82',
            'label' => __('Groups', 'event-manager'),
            'name' => 'user_groups',
            'type' => 'taxonomy',
            'instructions' => __('Select one or many user groups.', 'event-manager'),
            'required' => 1,
            'conditional_logic' => 0,
            'wrapper' => array(
                'width' => '',
                'class' => '',
                'id' => '',
            ),
            'taxonomy' => 'user_groups',
            'field_type' => 'checkbox',
            'allow_null' => 0,
            'add_term' => 1,
            'save_terms' => 1,
            'load_terms' => 0,
            'return_format' => 'id',
            'multiple' => 0,
        ),
    ),
    'location' => array(
        0 => array(
            0 => array(
                'param' => 'settings',
                'operator' => '==',
                'value' => '0',
            ),
        ),
    ),
    'menu_order' => 1,
    'position' => 'normal',
    'style' => 'default',
    'label_placement' => 'top',
    'instruction_placement' => 'label',
    'hide_on_screen' => '',
    'active' => 1,
    'description' => '',
));
}