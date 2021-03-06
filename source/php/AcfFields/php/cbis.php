<?php 

if (function_exists('acf_add_local_field_group')) {
    acf_add_local_field_group(array(
    'key' => 'group_5760fe97e3be1',
    'title' => __('CBIS', 'event-manager'),
    'fields' => array(
        0 => array(
            'key' => 'field_57eb99b4b4c2f',
            'label' => __('Daily import', 'event-manager'),
            'name' => 'cbis_daily_cron',
            'type' => 'true_false',
            'instructions' => '',
            'required' => 0,
            'conditional_logic' => 0,
            'wrapper' => array(
                'width' => '',
                'class' => '',
                'id' => '',
            ),
            'message' => __('Enable daily automatic import from CBIS', 'event-manager'),
            'default_value' => 1,
            'ui' => 0,
            'ui_on_text' => '',
            'ui_off_text' => '',
        ),
        1 => array(
            'key' => 'field_5812eee2085a8',
            'label' => __('Post status', 'event-manager'),
            'name' => 'cbis_post_status',
            'type' => 'radio',
            'instructions' => __('Select status of imported events.', 'event-manager'),
            'required' => 1,
            'conditional_logic' => 0,
            'wrapper' => array(
                'width' => '',
                'class' => '',
                'id' => '',
            ),
            'choices' => array(
                'draft' => __('Draft', 'event-manager'),
                'publish' => __('Published', 'event-manager'),
            ),
            'allow_null' => 0,
            'other_choice' => 0,
            'save_other_choice' => 0,
            'default_value' => 'publish',
            'layout' => 'vertical',
            'return_format' => 'value',
        ),
        2 => array(
            'key' => 'field_587648c2581e9',
            'label' => __('API keys', 'event-manager'),
            'name' => 'cbis_api_keys',
            'type' => 'repeater',
            'instructions' => '',
            'required' => 0,
            'conditional_logic' => 0,
            'wrapper' => array(
                'width' => '',
                'class' => '',
                'id' => '',
            ),
            'collapsed' => '',
            'min' => 0,
            'max' => 0,
            'layout' => 'block',
            'button_label' => __('Add key', 'event-manager'),
            'sub_fields' => array(
                0 => array(
                    'key' => 'field_58764910581ea',
                    'label' => __('API key', 'event-manager'),
                    'name' => 'cbis_api_product_key',
                    'type' => 'text',
                    'instructions' => '',
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
                    'key' => 'field_58764943581eb',
                    'label' => __('API GeoNode ID', 'event-manager'),
                    'name' => 'cbis_api_geonode_id',
                    'type' => 'text',
                    'instructions' => '',
                    'required' => 1,
                    'conditional_logic' => 0,
                    'wrapper' => array(
                        'width' => '50',
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
                    'key' => 'field_587649e8581ec',
                    'label' => __('Event ID', 'event-manager'),
                    'name' => 'cbis_event_id',
                    'type' => 'text',
                    'instructions' => __('ID of the category "events" used in CBIS.', 'event-manager'),
                    'required' => 1,
                    'conditional_logic' => 0,
                    'wrapper' => array(
                        'width' => '50',
                        'class' => '',
                        'id' => '',
                    ),
                    'default_value' => '',
                    'placeholder' => '',
                    'prepend' => '',
                    'append' => '',
                    'maxlength' => '',
                ),
                3 => array(
                    'key' => 'field_5878a78737d0b',
                    'label' => __('Location categories', 'event-manager'),
                    'name' => 'cbis_location_ids',
                    'type' => 'repeater',
                    'instructions' => __('Add one or many IDs to get locations from different categories.', 'event-manager'),
                    'required' => 0,
                    'conditional_logic' => 0,
                    'wrapper' => array(
                        'width' => '',
                        'class' => '',
                        'id' => '',
                    ),
                    'collapsed' => '',
                    'min' => 0,
                    'max' => 0,
                    'layout' => 'table',
                    'button_label' => __('Add', 'event-manager'),
                    'sub_fields' => array(
                        0 => array(
                            'key' => 'field_5878a7a737d0c',
                            'label' => __('Category ID', 'event-manager'),
                            'name' => 'cbis_location_cat_id',
                            'type' => 'number',
                            'instructions' => '',
                            'required' => 1,
                            'conditional_logic' => 0,
                            'wrapper' => array(
                                'width' => '20',
                                'class' => '',
                                'id' => '',
                            ),
                            'default_value' => '',
                            'placeholder' => '',
                            'prepend' => '',
                            'append' => '',
                            'min' => '',
                            'max' => '',
                            'step' => '',
                        ),
                        1 => array(
                            'key' => 'field_5878a80137d0d',
                            'label' => __('Category name', 'event-manager'),
                            'name' => 'cbis_location_name',
                            'type' => 'text',
                            'instructions' => __('Name of the category.', 'event-manager'),
                            'required' => 1,
                            'conditional_logic' => 0,
                            'wrapper' => array(
                                'width' => '80',
                                'class' => '',
                                'id' => '',
                            ),
                            'default_value' => '',
                            'placeholder' => __('e.g. Accommodations', 'event-manager'),
                            'prepend' => '',
                            'append' => '',
                            'maxlength' => '',
                        ),
                    ),
                ),
                4 => array(
                    'key' => 'field_5878a6f38bbaa',
                    'label' => __('Exclude categories', 'event-manager'),
                    'name' => 'cbis_filter_categories',
                    'type' => 'text',
                    'instructions' => __('Enter the name of the categories that you want to exclude from the import. Separate with commas.', 'event-manager'),
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
                5 => array(
                    'key' => 'field_5af2cdfa23801',
                    'label' => __('Default city', 'event-manager'),
                    'name' => 'cbis_default_city',
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
                6 => array(
                    'key' => 'field_5878a7338bbab',
                    'label' => __('Default user groups', 'event-manager'),
                    'name' => 'cbis_publishing_groups',
                    'type' => 'taxonomy',
                    'instructions' => __('Select the user groups that you want to set as default to imported posts.', 'event-manager'),
                    'required' => 0,
                    'conditional_logic' => 0,
                    'wrapper' => array(
                        'width' => '',
                        'class' => '',
                        'id' => '',
                    ),
                    'taxonomy' => 'user_groups',
                    'field_type' => 'checkbox',
                    'allow_null' => 0,
                    'add_term' => 0,
                    'save_terms' => 1,
                    'load_terms' => 0,
                    'return_format' => 'id',
                    'multiple' => 0,
                ),
            ),
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