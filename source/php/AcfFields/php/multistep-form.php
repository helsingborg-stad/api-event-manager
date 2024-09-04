<?php 

if (function_exists('acf_add_local_field_group')) {
    acf_add_local_field_group(array(
    'key' => 'group_6627a5e16d84f',
    'title' => __('Configure Multistep Form', 'api-event-manager'),
    'fields' => array(
        0 => array(
            'key' => 'field_662a51f8a599e',
            'label' => __('Form Steps', 'api-event-manager'),
            'name' => '',
            'aria-label' => '',
            'type' => 'tab',
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
            'placement' => 'top',
            'endpoint' => 0,
        ),
        1 => array(
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
                    ),
                    'default_value' => array(
                    ),
                    'return_format' => 'value',
                    'multiple' => 1,
                    'allow_custom' => 0,
                    'placeholder' => '',
                    'allow_null' => 0,
                    'ui' => 1,
                    'ajax' => 0,
                    'search_placeholder' => '',
                    'parent_repeater' => 'field_6627a5e312422',
                ),
                3 => array(
                    'key' => 'field_664dea524e1e2',
                    'label' => __('Display Post title', 'api-event-manager'),
                    'name' => 'formStepIncludesPostTitle',
                    'aria-label' => '',
                    'type' => 'true_false',
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
                    'message' => __('If this step should include a field for the posts title', 'api-event-manager'),
                    'default_value' => 0,
                    'ui' => 0,
                    'ui_on_text' => '',
                    'ui_off_text' => '',
                    'parent_repeater' => 'field_6627a5e312422',
                ),
            ),
            'acfe_repeater_stylised_button' => 0,
        ),
        2 => array(
            'key' => 'field_662a5218a599f',
            'label' => __('Submission Configuration', 'api-event-manager'),
            'name' => '',
            'aria-label' => '',
            'type' => 'tab',
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
            'placement' => 'top',
            'endpoint' => 0,
        ),
        3 => array(
            'key' => 'field_6627a79912426',
            'label' => __('Select Post Type', 'api-event-manager'),
            'name' => 'saveToPostType',
            'aria-label' => '',
            'type' => 'posttype_select',
            'instructions' => __('The post type that the user should be able to add to or modify.', 'api-event-manager'),
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
        4 => array(
            'key' => 'field_662a4f4d25fe6',
            'label' => __('In Progress Post Status', 'api-event-manager'),
            'name' => 'saveToPostTypeStatus',
            'aria-label' => '',
            'type' => 'select',
            'instructions' => __('The post status to use when a form hasen\'t been reviewed by the user.', 'api-event-manager'),
            'required' => 0,
            'conditional_logic' => 0,
            'wrapper' => array(
                'width' => '50',
                'class' => '',
                'id' => '',
            ),
            'is_publicly_hidden' => 0,
            'is_privately_hidden' => 0,
            'choices' => array(
                'draft' => __('Draft', 'api-event-manager'),
                'pending' => __('Pending Review', 'api-event-manager'),
                'private' => __('Private', 'api-event-manager'),
                'publish' => __('Published', 'api-event-manager'),
            ),
            'default_value' => __('draft', 'api-event-manager'),
            'return_format' => 'value',
            'multiple' => 0,
            'allow_null' => 0,
            'ui' => 0,
            'ajax' => 0,
            'placeholder' => '',
            'allow_custom' => 0,
            'search_placeholder' => '',
        ),
        5 => array(
            'key' => 'field_664cac4d3a132',
            'label' => __('Reviewed Post Status', 'api-event-manager'),
            'name' => 'reviewedPostTypeStatus',
            'aria-label' => '',
            'type' => 'select',
            'instructions' => __('The post status to use when a form have been reviewed by the user.', 'api-event-manager'),
            'required' => 1,
            'conditional_logic' => 0,
            'wrapper' => array(
                'width' => '50',
                'class' => '',
                'id' => '',
            ),
            'is_publicly_hidden' => 0,
            'is_privately_hidden' => 0,
            'choices' => array(
                'draft' => __('Draft', 'api-event-manager'),
                'pending' => __('Pending Review', 'api-event-manager'),
                'private' => __('Private', 'api-event-manager'),
                'publish' => __('Published', 'api-event-manager'),
            ),
            'default_value' => false,
            'return_format' => 'value',
            'multiple' => 0,
            'allow_null' => 0,
            'ui' => 0,
            'ajax' => 0,
            'placeholder' => '',
            'allow_custom' => 0,
            'search_placeholder' => '',
        ),
        6 => array(
            'key' => 'field_662a5230a59a0',
            'label' => __('Form Completion', 'api-event-manager'),
            'name' => '',
            'aria-label' => '',
            'type' => 'tab',
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
            'placement' => 'top',
            'endpoint' => 0,
        ),
        7 => array(
            'key' => 'field_664de6eb4fffd',
            'label' => __('Enable summary', 'api-event-manager'),
            'name' => 'hasSummaryStep',
            'aria-label' => '',
            'type' => 'true_false',
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
            'message' => __('Wheter this form should include a summary step before acquiring the final post status.', 'api-event-manager'),
            'default_value' => 1,
            'ui' => 0,
            'ui_on_text' => '',
            'ui_off_text' => '',
        ),
        8 => array(
            'key' => 'field_664de74a4fffe',
            'label' => __('Summary Title', 'api-event-manager'),
            'name' => 'summaryTitle',
            'aria-label' => '',
            'type' => 'text',
            'instructions' => '',
            'required' => 0,
            'conditional_logic' => array(
                0 => array(
                    0 => array(
                        'field' => 'field_664de6eb4fffd',
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
            'is_publicly_hidden' => 0,
            'is_privately_hidden' => 0,
            'default_value' => '',
            'maxlength' => '',
            'placeholder' => '',
            'prepend' => '',
            'append' => '',
        ),
        9 => array(
            'key' => 'field_664de76b4ffff',
            'label' => __('Summary Lead', 'api-event-manager'),
            'name' => 'summaryLead',
            'aria-label' => '',
            'type' => 'wysiwyg',
            'instructions' => '',
            'required' => 0,
            'conditional_logic' => array(
                0 => array(
                    0 => array(
                        'field' => 'field_664de6eb4fffd',
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
            'is_publicly_hidden' => 0,
            'is_privately_hidden' => 0,
            'default_value' => '',
            'tabs' => 'all',
            'toolbar' => 'full',
            'media_upload' => 1,
            'delay' => 0,
        ),
        10 => array(
            'key' => 'field_662a6546a687a',
            'label' => __('Security', 'api-event-manager'),
            'name' => '',
            'aria-label' => '',
            'type' => 'tab',
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
            'placement' => 'top',
            'endpoint' => 0,
        ),
        11 => array(
            'key' => 'field_662a6557a687b',
            'label' => __('Public Access', 'api-event-manager'),
            'name' => 'isPublicForm',
            'aria-label' => '',
            'type' => 'true_false',
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
            'message' => __('This form may be used by unauthenticated visitors', 'api-event-manager'),
            'default_value' => 1,
            'ui' => 0,
            'ui_on_text' => '',
            'ui_off_text' => '',
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
    'acfe_display_title' => '',
    'acfe_autosync' => '',
    'acfe_form' => 0,
    'acfe_meta' => '',
    'acfe_note' => '',
));
}