<?php 


if (function_exists('acf_add_local_field_group')) {

    acf_add_local_field_group(array(
    'key' => 'group_660cec468b833',
    'title' => __('Plugin Settings', 'api-event-manager'),
    'fields' => array(
        0 => array(
            'key' => 'field_660cec46b22c4',
            'label' => __('Cleanup', 'api-event-manager'),
            'name' => 'cleanup',
            'aria-label' => '',
            'type' => 'group',
            'instructions' => '',
            'required' => 0,
            'conditional_logic' => 0,
            'wrapper' => array(
                'width' => '',
                'class' => '',
                'id' => '',
            ),
            'layout' => 'block',
            'acfe_seamless_style' => 0,
            'acfe_group_modal' => 0,
            'sub_fields' => array(
                0 => array(
                    'key' => 'field_660ceca3b22c5',
                    'label' => __('Clean up expired events', 'api-event-manager'),
                    'name' => 'cleanupExpiredEvents',
                    'aria-label' => '',
                    'type' => 'true_false',
                    'instructions' => __('Enables automatic deletion of expired events', 'api-event-manager'),
                    'required' => 0,
                    'conditional_logic' => 0,
                    'wrapper' => array(
                        'width' => '',
                        'class' => '',
                        'id' => '',
                    ),
                    'message' => '',
                    'default_value' => 0,
                    'ui' => 0,
                    'ui_on_text' => '',
                    'ui_off_text' => '',
                ),
                1 => array(
                    'key' => 'field_660ced26b22c6',
                    'label' => __('Delete expired posts after', 'api-event-manager'),
                    'name' => 'deleteExpiredPostsAfter',
                    'aria-label' => '',
                    'type' => 'select',
                    'instructions' => '',
                    'required' => 1,
                    'conditional_logic' => array(
                        0 => array(
                            0 => array(
                                'field' => 'field_660ceca3b22c5',
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
                    'choices' => array(
                        '## Weeks' => __('## Weeks', 'api-event-manager'),
                        '-1 week' => __('1 week', 'api-event-manager'),
                        '-2 weeks' => __('2 weeks', 'api-event-manager'),
                        '-3 weeks' => __('3 weeks', 'api-event-manager'),
                        '## Months' => __('## Months', 'api-event-manager'),
                        '-1 month' => __('1 month', 'api-event-manager'),
                        '-2 months' => __('2 months', 'api-event-manager'),
                        '-3 months' => __('3 months', 'api-event-manager'),
                        '-6 months' => __('6 months', 'api-event-manager'),
                    ),
                    'default_value' => __('-1 month', 'api-event-manager'),
                    'return_format' => 'value',
                    'multiple' => 0,
                    'allow_null' => 0,
                    'ui' => 0,
                    'ajax' => 0,
                    'placeholder' => '',
                    'allow_custom' => 0,
                    'search_placeholder' => '',
                ),
            ),
            'acfe_group_modal_close' => 0,
            'acfe_group_modal_button' => '',
            'acfe_group_modal_size' => 'large',
        ),
    ),
    'location' => array(
        0 => array(
            0 => array(
                'param' => 'options_page',
                'operator' => '==',
                'value' => 'event-manager-settings',
            ),
        ),
    ),
    'menu_order' => 0,
    'position' => 'normal',
    'style' => 'default',
    'label_placement' => 'left',
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