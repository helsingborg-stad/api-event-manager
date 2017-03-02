<?php 

if (function_exists('acf_add_local_field_group')) {
    acf_add_local_field_group(array(
    'key' => 'group_575fe32901927',
    'title' => 'Event manager settings',
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
            'key' => 'field_57fb3d55d2535',
            'label' => __('Default city', 'event-manager'),
            'name' => 'default_city',
            'type' => 'text',
            'instructions' => __('If essential address components are missing during import, this city will be used as default.', 'event-manager'),
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
            'maxlength' => '',
        ),
        2 => array(
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
        ),
        3 => array(
            'key' => 'field_588605e9078dc',
            'label' => 'Groups',
            'name' => 'event_group_select',
            'type' => 'checkbox',
            'instructions' => 'Activate user groups on selected post types.',
            'required' => 0,
            'conditional_logic' => 0,
            'wrapper' => array(
                'width' => '',
                'class' => '',
                'id' => '',
            ),
            'choices' => array(
                'event' => 'Events',
                'location' => 'Locations',
                'contact' => 'Contacts',
                'sponsor' => 'Sponsors',
                'package' => 'Packages',
                'membership-card' => 'Membership cards',
                'guide' => 'Guides',
            ),
            'default_value' => array(
                0 => 'event',
            ),
            'layout' => 'vertical',
            'toggle' => 0,
            'return_format' => 'value',
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
    'active' => 1,
    'description' => '',
    'local' => 'json',
));
}