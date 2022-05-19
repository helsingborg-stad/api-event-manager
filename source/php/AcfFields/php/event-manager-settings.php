<?php 

if (function_exists('acf_add_local_field_group')) {
    acf_add_local_field_group(array(
    'key' => 'group_575fe32901927',
    'title' => __('Event manager settings', 'event-manager'),
    'fields' => array(
        0 => array(
            'key' => 'field_575fe34355309',
            'label' => __('Google Api key', 'event-manager'),
            'name' => 'google_geocode_api_key',
            'type' => 'text',
            'instructions' => __('API-services required:<br> Google Maps JavaScript API<br> Google Maps Geocoding API<br> Google Places API Web Service', 'event-manager'),
            'required' => 1,
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
            'maxlength' => '',
        ),
        1 => array(
            'key' => 'field_580db5e4967d6',
            'label' => __('Import warning', 'event-manager'),
            'name' => 'import_warning',
            'type' => 'true_false',
            'instructions' => '',
            'required' => 0,
            'conditional_logic' => 0,
            'wrapper' => array(
                'width' => '',
                'class' => '',
                'id' => '',
            ),
            'message' => __('Show notification if no events have been imported for a week or more.', 'event-manager'),
            'default_value' => 1,
            'ui' => 0,
            'ui_on_text' => '',
            'ui_off_text' => '',
        ),
        2 => array(
            'key' => 'field_588605e9078dc',
            'label' => __('Groups', 'event-manager'),
            'name' => 'event_group_select',
            'type' => 'checkbox',
            'instructions' => __('Activate user groups on selected post types.', 'event-manager'),
            'required' => 0,
            'conditional_logic' => 0,
            'wrapper' => array(
                'width' => '',
                'class' => '',
                'id' => '',
            ),
            'choices' => array(
                'event' => __('Events', 'event-manager'),
                'location' => __('Locations', 'event-manager'),
                'sponsor' => __('Sponsors', 'event-manager'),
                'membership-card' => __('Membership cards', 'event-manager'),
                'organizer' => __('Organizers', 'event-manager'),
                'guide' => __('Guides', 'event-manager'),
                'interactive_guide' => __('Interactive Guides', 'event-manager'),
            ),
            'allow_custom' => 0,
            'default_value' => array(
                0 => 'event',
            ),
            'layout' => 'vertical',
            'toggle' => 0,
            'return_format' => 'value',
            'save_custom' => 0,
        ),
    ),
    'location' => array(
        0 => array(
            0 => array(
                'param' => 'options_page',
                'operator' => '==',
                'value' => 'acf-options-options',
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