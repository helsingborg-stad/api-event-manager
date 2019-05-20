<?php 



    'key' => 'group_5ce25720a2508',
    'title' => 'Open Library Solutions',
    'fields' => array(
        0 => array(
            'key' => 'field_5ce2583bff1e0',
            'label' => __('Daily import', 'event-manager'),
            'name' => 'ols_daily_cron',
            'type' => 'true_false',
            'instructions' => '',
            'required' => 0,
            'conditional_logic' => 0,
            'wrapper' => array(
                'width' => '',
                'class' => '',
                'id' => '',
            ),
            'message' => '',
            'default_value' => 1,
            'ui' => 1,
            'ui_on_text' => '',
            'ui_off_text' => '',
        ),
        1 => array(
            'key' => 'field_5ce2596d091d0',
            'label' => __('Post status', 'event-manager'),
            'name' => 'ols_post_status',
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
            'default_value' => 'publish',
            'layout' => 'vertical',
            'return_format' => 'value',
            'save_other_choice' => 0,
        ),
        2 => array(
            'key' => 'field_5ce25aa654f07',
            'label' => __('API links', 'event-manager'),
            'name' => 'ols_api_urls',
            'type' => 'repeater',
            'instructions' => __('Add API links to import events from Open Library Solutions.', 'event-manager'),
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
            'button_label' => '',
            'sub_fields' => array(
                0 => array(
                    'key' => 'field_5ce25b5154f08',
                    'label' => __('API link', 'event-manager'),
                    'name' => 'ols_api_url',
                    'type' => 'url',
                    'instructions' => __('Add Open LIbrary Solutions base API-URL without query string.', 'event-manager'),
                    'required' => 1,
                    'conditional_logic' => 0,
                    'wrapper' => array(
                        'width' => '',
                        'class' => '',
                        'id' => '',
                    ),
                    'default_value' => '',
                    'placeholder' => '',
                ),
                1 => array(
                    'key' => 'field_5ce25bda54f09',
                    'label' => __('API key', 'event-manager'),
                    'name' => 'ols_api_key',
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
                2 => array(
                    'key' => 'field_5ce25c0754f0a',
                    'label' => __('Group ID', 'event-manager'),
                    'name' => 'ols_group_id',
                    'type' => 'text',
                    'instructions' => __('Get events from a specified library group.', 'event-manager'),
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
    'menu_order' => 0,
    'position' => 'normal',
    'style' => 'default',
    'label_placement' => 'top',
    'instruction_placement' => 'label',
    'hide_on_screen' => '',
    'active' => true,
    'description' => '',
));
