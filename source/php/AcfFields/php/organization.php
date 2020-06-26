<?php 

if (function_exists('acf_add_local_field_group')) {
    acf_add_local_field_group(array(
    'key' => 'group_5ef1b6d006a4f',
    'title' => __('Organisation', 'event-manager'),
    'fields' => array(
        0 => array(
            'key' => 'field_5ef1b6db26521',
            'label' => __('Organisation', 'event-manager'),
            'name' => 'organisation',
            'type' => 'taxonomy',
            'instructions' => '',
            'required' => 0,
            'conditional_logic' => 0,
            'wrapper' => array(
                'width' => '',
                'class' => '',
                'id' => '',
            ),
            'taxonomy' => 'user_groups',
            'field_type' => 'multi_select',
            'allow_null' => 1,
            'add_term' => 0,
            'save_terms' => 0,
            'load_terms' => 0,
            'return_format' => 'id',
            'multiple' => 0,
        ),
    ),
    'location' => array(
        0 => array(
            0 => array(
                'param' => 'user_role',
                'operator' => '==',
                'value' => 'all',
            ),
        ),
        1 => array(
            0 => array(
                'param' => 'taxonomy',
                'operator' => '==',
                'value' => 'guidegroup',
            ),
        ),
    ),
    'menu_order' => 0,
    'position' => 'normal',
    'style' => 'default',
    'label_placement' => 'top',
    'instruction_placement' => 'label',
    'hide_on_screen' => '',
    'active' => true,
    'description' => '',
));
}