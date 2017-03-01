<?php 

if (function_exists('acf_add_local_field_group')) {
    acf_add_local_field_group(array(
    'key' => 'group_58b6e40e5a8f4',
    'title' => __('Min fältgrupp', 'event-manager'),
    'fields' => array(
        0 => array(
            'key' => 'field_58b6e414dded1',
            'label' => __('Mitt fält', 'event-manager'),
            'name' => 'mitt_falt',
            'type' => 'text',
            'instructions' => __('Detta är min instruktion', 'event-manager'),
            'required' => 0,
            'conditional_logic' => 0,
            'wrapper' => array(
                'width' => '',
                'class' => '',
                'id' => '',
            ),
            'default_value' => __('Inget standardväde', 'event-manager'),
            'maxlength' => 10,
            'placeholder' => __('Hej', 'event-manager'),
            'prepend' => __('Ett', 'event-manager'),
            'append' => __('Två', 'event-manager'),
        ),
    ),
    'location' => array(
        0 => array(
            0 => array(
                'param' => 'post_type',
                'operator' => '==',
                'value' => 'post',
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
    'local' => 'php',
));
}