<?php 

if (function_exists('acf_add_local_field_group')) {
    acf_add_local_field_group(array(
    'key' => 'group_65a4f5a847d62',
    'title' => __('Organization Fields', 'api-event-manager'),
    'fields' => array(
        0 => array(
            'key' => 'field_65a4f5a8f4b18',
            'label' => __('Address', 'api-event-manager'),
            'name' => 'address',
            'aria-label' => '',
            'type' => 'google_map',
            'instructions' => '',
            'required' => 0,
            'conditional_logic' => 0,
            'wrapper' => array(
                'width' => '',
                'class' => '',
                'id' => '',
            ),
            'center_lat' => '',
            'center_lng' => '',
            'zoom' => '',
            'height' => '',
        ),
        1 => array(
            'key' => 'field_65a4f5c8f4b19',
            'label' => __('Url', 'api-event-manager'),
            'name' => 'url',
            'aria-label' => '',
            'type' => 'url',
            'instructions' => '',
            'required' => 0,
            'conditional_logic' => 0,
            'wrapper' => array(
                'width' => '',
                'class' => '',
                'id' => '',
            ),
            'default_value' => '',
            'placeholder' => '',
        ),
        2 => array(
            'key' => 'field_65a4f5d1f4b1a',
            'label' => __('Email', 'api-event-manager'),
            'name' => 'email',
            'aria-label' => '',
            'type' => 'email',
            'instructions' => '',
            'required' => 0,
            'conditional_logic' => 0,
            'wrapper' => array(
                'width' => '',
                'class' => '',
                'id' => '',
            ),
            'default_value' => '',
            'placeholder' => '',
            'prepend' => '',
            'append' => '',
        ),
        3 => array(
            'key' => 'field_65a4f5dff4b1b',
            'label' => __('Telephone', 'api-event-manager'),
            'name' => 'telephone',
            'aria-label' => '',
            'type' => 'text',
            'instructions' => '',
            'required' => 0,
            'conditional_logic' => 0,
            'wrapper' => array(
                'width' => '',
                'class' => '',
                'id' => '',
            ),
            'default_value' => '',
            'maxlength' => '',
            'placeholder' => '',
            'prepend' => '',
            'append' => '',
        ),
        4 => array(
            'key' => 'field_6628f2e0c4a53',
            'label' => __('Verified', 'api-event-manager'),
            'name' => 'verified',
            'aria-label' => '',
            'type' => 'true_false',
            'instructions' => __('Determines if members of this organization are allowed to publish events without going through the review process.', 'api-event-manager'),
            'required' => 0,
            'conditional_logic' => 0,
            'wrapper' => array(
                'width' => '',
                'class' => '',
                'id' => '',
            ),
            'is_publicly_hidden' => 1,
            'is_privately_hidden' => 0,
            'message' => '',
            'default_value' => 0,
            'ui' => 0,
            'ui_on_text' => '',
            'ui_off_text' => '',
        ),
    ),
    'location' => array(
        0 => array(
            0 => array(
                'param' => 'taxonomy',
                'operator' => '==',
                'value' => 'organization',
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
    'acfe_display_title' => '',
    'acfe_autosync' => '',
    'acfe_form' => 0,
    'acfe_meta' => '',
    'acfe_note' => '',
));
}