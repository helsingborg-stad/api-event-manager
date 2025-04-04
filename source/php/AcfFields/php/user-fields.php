<?php 


if (function_exists('acf_add_local_field_group')) {

    acf_add_local_field_group(array(
    'key' => 'group_660d1667d32a0',
    'title' => __('Event Manager - User fields', 'api-event-manager'),
    'fields' => array(
        0 => array(
            'key' => 'field_660d16683130c',
            'label' => __('Organizations', 'api-event-manager'),
            'name' => 'organizations',
            'aria-label' => '',
            'type' => 'acfe_taxonomy_terms',
            'instructions' => __('Determines which organization(s) this user is allowed to create events for. Also restricts the user from viewing, editing and deleting events belonging to any other organizer than the ones selected here.', 'api-event-manager'),
            'required' => 0,
            'conditional_logic' => 0,
            'wrapper' => array(
                'width' => '',
                'class' => '',
                'id' => '',
            ),
            'taxonomy' => array(
                0 => 'organization',
            ),
            'allow_terms' => '',
            'allow_level' => '',
            'field_type' => 'select',
            'default_value' => array(
            ),
            'return_format' => 'id',
            'ui' => 1,
            'allow_null' => 1,
            'placeholder' => '',
            'multiple' => 1,
            'ajax' => 0,
            'save_terms' => 0,
            'load_terms' => 0,
            'choices' => array(
            ),
            'search_placeholder' => '',
            'layout' => '',
            'toggle' => 0,
            'allow_custom' => 0,
            'other_choice' => 0,
        ),
    ),
    'location' => array(
        0 => array(
            0 => array(
                'param' => 'user_form',
                'operator' => '==',
                'value' => 'edit',
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