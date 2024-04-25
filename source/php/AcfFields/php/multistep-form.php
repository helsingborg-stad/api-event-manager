<?php 

if (function_exists('acf_add_local_field_group')) {
    acf_add_local_field_group(array(
    'key' => 'group_6627a5e16d84f',
    'title' => 'Configure Multistep Form',
    'fields' => array(
        0 => array(
            'key' => 'field_6627a5e312422',
            'label' => __('Add Form Step', 'api-event-manager'),
            'name' => 'formSteps',
            'aria-label' => '',
            'type' => 'repeater',
            'instructions' => __('Add the steps that should be displayed in this form.', 'api-event-manager'),
            'required' => 0,
            'conditional_logic' => 0,
            'wrapper' => array(
                'width' => '',
                'class' => '',
                'id' => '',
            ),
            'is_publicly_hidden' => 0,
            'is_privately_hidden' => 0,
            'layout' => 'row',
            'pagination' => 0,
            'min' => 0,
            'max' => 0,
            'collapsed' => 'field_6627a6b312423',
            'button_label' => __('Add Step', 'api-event-manager'),
            'rows_per_page' => 20,
            'sub_fields' => array(
                0 => array(
                    'key' => 'field_6627a6b312423',
                    'label' => __('Form Step Title', 'api-event-manager'),
                    'name' => 'formStepTitle',
                    'aria-label' => '',
                    'type' => 'text',
                    'instructions' => __('By leaving this blank, the configuration of the fieldgroup will be used.', 'api-event-manager'),
                    'required' => 1,
                    'conditional_logic' => 0,
                    'wrapper' => array(
                        'width' => '',
                        'class' => '',
                        'id' => '',
                    ),
                    'is_publicly_hidden' => 0,
                    'is_privately_hidden' => 0,
                    'default_value' => '',
                    'maxlength' => '',
                    'placeholder' => '',
                    'prepend' => '',
                    'append' => '',
                    'parent_repeater' => 'field_6627a5e312422',
                ),
                1 => array(
                    'key' => 'field_6627a6e112424',
                    'label' => __('From Step Content', 'api-event-manager'),
                    'name' => 'formStepContent',
                    'aria-label' => '',
                    'type' => 'wysiwyg',
                    'instructions' => __('By leaving this blank, the configuration of the fieldgroup will be used.', 'api-event-manager'),
                    'required' => 0,
                    'conditional_logic' => 0,
                    'wrapper' => array(
                        'width' => '',
                        'class' => '',
                        'id' => '',
                    ),
                    'is_publicly_hidden' => 0,
                    'is_privately_hidden' => 0,
                    'default_value' => '',
                    'tabs' => 'all',
                    'toolbar' => 'basic',
                    'media_upload' => 0,
                    'delay' => 0,
                    'parent_repeater' => 'field_6627a5e312422',
                ),
                2 => array(
                    'key' => 'field_6627a6f212425',
                    'label' => __('Form Step Group', 'api-event-manager'),
                    'name' => 'formStepGroup',
                    'aria-label' => '',
                    'type' => 'select',
                    'instructions' => '',
                    'required' => 1,
                    'conditional_logic' => 0,
                    'wrapper' => array(
                        'width' => '',
                        'class' => '',
                        'id' => '',
                    ),
                    'is_publicly_hidden' => 0,
                    'is_privately_hidden' => 0,
                    'choices' => array(
                        'group_6627a5e16d84f' => __('Event Form: Configure Multistep Form', 'api-event-manager'),
                        'group_661e41bb1781f' => __('Events: Discover the Essentials of Your Event', 'api-event-manager'),
                        'group_65a115157a046' => __('Events: Event Fields', 'api-event-manager'),
                        'group_661e425070deb' => __('Events: When and where?', 'api-event-manager'),
                    ),
                    'default_value' => false,
                    'return_format' => 'value',
                    'multiple' => 0,
                    'allow_null' => 0,
                    'ui' => 0,
                    'ajax' => 0,
                    'placeholder' => '',
                    'parent_repeater' => 'field_6627a5e312422',
                ),
            ),
        ),
        1 => array(
            'key' => 'field_6627a79912426',
            'label' => __('Select Post Type', 'api-event-manager'),
            'name' => 'saveToPostType',
            'aria-label' => '',
            'type' => 'posttype_select',
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
            'default_value' => '',
            'allow_null' => 0,
            'multiple' => 0,
            'placeholder' => '',
            'disabled' => 0,
            'readonly' => 0,
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