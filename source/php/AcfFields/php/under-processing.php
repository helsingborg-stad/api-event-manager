<?php 

if (function_exists('acf_add_local_field_group')) {
    acf_add_local_field_group(array(
    'key' => 'group_5b2b60ac1bb08',
    'title' => __('Under processing', 'event-manager'),
    'fields' => array(
        0 => array(
            'key' => 'field_5b2b60b9094bd',
            'label' => '',
            'name' => 'event_under_processing',
            'type' => 'true_false',
            'instructions' => __('Select if this event is under processing.', 'event-manager'),
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
            'ui_on_text' => '',
            'ui_off_text' => '',
        ),
        1 => array(
            'key' => 'field_5b2b60e8094be',
            'label' => __('Comment', 'event-manager'),
            'name' => 'event_processing_comment',
            'type' => 'textarea',
            'instructions' => __('Write a comment.', 'event-manager'),
            'required' => 0,
            'conditional_logic' => array(
                0 => array(
                    0 => array(
                        'field' => 'field_5b2b60b9094bd',
                        'operator' => '==',
                        'value' => '1',
                    ),
                ),
            ),
            'wrapper' => array(
                'width' => '',
                'class' => '',
                'id' => '',
            ),
            'default_value' => '',
            'placeholder' => '',
            'maxlength' => '',
            'rows' => '',
            'new_lines' => '',
        ),
    ),
    'location' => array(
        0 => array(
            0 => array(
                'param' => 'post_type',
                'operator' => '==',
                'value' => 'event',
            ),
            1 => array(
                'param' => 'post_status',
                'operator' => '!=',
                'value' => 'publish',
            ),
        ),
    ),
    'menu_order' => 99,
    'position' => 'normal',
    'style' => 'default',
    'label_placement' => 'top',
    'instruction_placement' => 'label',
    'hide_on_screen' => '',
    'active' => 1,
    'description' => '',
));
}