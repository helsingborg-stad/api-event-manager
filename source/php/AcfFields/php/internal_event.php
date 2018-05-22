<?php 

if (function_exists('acf_add_local_field_group')) {
    acf_add_local_field_group(array(
    'key' => 'group_5afecb1bf1b8d',
    'title' => __('Internal event', 'event-manager'),
    'fields' => array(
        0 => array(
            'key' => 'field_5afed0810c363',
            'label' => '',
            'name' => 'internal_event',
            'type' => 'true_false',
            'instructions' => __('Select if this is an internal event.', 'event-manager'),
            'required' => 0,
            'conditional_logic' => 0,
            'wrapper' => array(
                'width' => '',
                'class' => '',
                'id' => '',
            ),
            'message' => '',
            'default_value' => 0,
            'ui' => 1,
            'ui_on_text' => __('Yes', 'event-manager'),
            'ui_off_text' => __('No', 'event-manager'),
        ),
    ),
    'location' => array(
        0 => array(
            0 => array(
                'param' => 'post_type',
                'operator' => '==',
                'value' => 'event',
            ),
        ),
    ),
    'menu_order' => 10,
    'position' => 'side',
    'style' => 'default',
    'label_placement' => 'top',
    'instruction_placement' => 'label',
    'hide_on_screen' => '',
    'active' => 1,
    'description' => '',
));
}