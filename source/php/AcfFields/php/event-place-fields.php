<?php 

if (function_exists('acf_add_local_field_group')) {
    acf_add_local_field_group(array(
    'key' => 'group_696e24e564997',
    'title' => __('Place', 'api-event-manager'),
    'fields' => array(
        0 => array(
            'key' => 'field_696e24e5660ed',
            'label' => __('Attendance mode', 'api-event-manager'),
            'name' => 'attendancemode',
            'aria-label' => '',
            'type' => 'button_group',
            'instructions' => '',
            'required' => 0,
            'conditional_logic' => 0,
            'wrapper' => array(
                'width' => '',
                'class' => '',
                'id' => '',
            ),
            'is_publicly_hidden' => 0,
            'is_privately_hidden' => 0,
            'choices' => array(
                'offline' => __('Offline', 'api-event-manager'),
                'online' => __('Online', 'api-event-manager'),
            ),
            'default_value' => __('offline', 'api-event-manager'),
            'return_format' => 'value',
            'allow_null' => 0,
            'allow_in_bindings' => 0,
            'layout' => 'horizontal',
        ),
        1 => array(
            'key' => 'field_696e24e566ca3',
            'label' => __('Location name', 'api-event-manager'),
            'name' => 'locationName',
            'aria-label' => '',
            'type' => 'text',
            'instructions' => '',
            'required' => 0,
            'conditional_logic' => array(
                0 => array(
                    0 => array(
                        'field' => 'field_696e24e5660ed',
                        'operator' => '==',
                        'value' => 'offline',
                    ),
                ),
            ),
            'wrapper' => array(
                'width' => '',
                'class' => '',
                'id' => '',
            ),
            'is_publicly_hidden' => 0,
            'is_privately_hidden' => 0,
            'default_value' => '',
            'maxlength' => '',
            'allow_in_bindings' => 0,
            'placeholder' => '',
            'prepend' => '',
            'append' => '',
        ),
        2 => array(
            'key' => 'field_696e24e567083',
            'label' => __('Location address', 'api-event-manager'),
            'name' => 'locationAddress',
            'aria-label' => '',
            'type' => 'google_map',
            'instructions' => '',
            'required' => 0,
            'conditional_logic' => array(
                0 => array(
                    0 => array(
                        'field' => 'field_696e24e5660ed',
                        'operator' => '==',
                        'value' => 'offline',
                    ),
                ),
            ),
            'wrapper' => array(
                'width' => '',
                'class' => '',
                'id' => '',
            ),
            'is_publicly_hidden' => 0,
            'is_privately_hidden' => 0,
            'center_lat' => '56.0467',
            'center_lng' => '12.6944',
            'zoom' => '',
            'height' => '',
            'allow_in_bindings' => 0,
        ),
        3 => array(
            'key' => 'field_696e24e56746f',
            'label' => __('Online attendence url', 'api-event-manager'),
            'name' => 'onlineAttendenceUrl',
            'aria-label' => '',
            'type' => 'url',
            'instructions' => '',
            'required' => 0,
            'conditional_logic' => array(
                0 => array(
                    0 => array(
                        'field' => 'field_696e24e5660ed',
                        'operator' => '==',
                        'value' => 'online',
                    ),
                ),
            ),
            'wrapper' => array(
                'width' => '',
                'class' => '',
                'id' => '',
            ),
            'is_publicly_hidden' => 0,
            'is_privately_hidden' => 0,
            'default_value' => '',
            'allow_in_bindings' => 0,
            'placeholder' => '',
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
    'menu_order' => 3,
    'position' => 'acf_after_title',
    'style' => 'default',
    'label_placement' => 'left',
    'instruction_placement' => 'label',
    'hide_on_screen' => array(
        0 => 'permalink',
        1 => 'the_content',
        2 => 'excerpt',
        3 => 'discussion',
        4 => 'comments',
        5 => 'slug',
        6 => 'format',
        7 => 'page_attributes',
        8 => 'featured_image',
        9 => 'categories',
        10 => 'tags',
        11 => 'send-trackbacks',
    ),
    'active' => true,
    'description' => '',
    'show_in_rest' => 1,
    'display_title' => '',
    'allow_ai_access' => false,
    'ai_description' => '',
));
}