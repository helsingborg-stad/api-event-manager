<?php 

if (function_exists('acf_add_local_field_group')) {
    acf_add_local_field_group(array(
    'key' => 'group_65eebfb9c35a7',
    'title' => __('Event Form Display', 'api-event-manager'),
    'fields' => array(
        0 => array(
            'key' => 'field_65eebfbb7caf7',
            'label' => __('Field Groups', 'api-event-manager'),
            'name' => 'display_field_groups',
            'aria-label' => '',
            'type' => 'checkbox',
            'instructions' => '',
            'required' => 1,
            'conditional_logic' => 0,
            'wrapper' => array(
                'width' => '',
                'class' => '',
                'id' => '',
            ),
            'choices' => array(
                'a' => __('A', 'api-event-manager'),
                'b' => __('B', 'api-event-manager'),
            ),
            'default_value' => array(
            ),
            'return_format' => 'array',
            'allow_custom' => 0,
            'layout' => 'horizontal',
            'toggle' => 1,
            'save_custom' => 0,
            'custom_choice_button_text' => 'Add new choice',
        ),
    ),
    'location' => array(
        0 => array(
            0 => array(
                'param' => 'post_type',
                'operator' => '==',
                'value' => 'mod-event-form',
            ),
        ),
        1 => array(
            0 => array(
                'param' => 'block',
                'operator' => '==',
                'value' => 'acf/event-form',
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
    'show_in_rest' => 0,
));
}