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
            'type' => 'taxonomy',
            'instructions' => __('Determines which organization(s) this user is allowed to create events for. Also restricts the user from viewing, editing and deleting events belonging to any other organizer than the ones selected here.', 'api-event-manager'),
            'required' => 0,
            'conditional_logic' => 0,
            'wrapper' => array(
                'width' => '',
                'class' => '',
                'id' => '',
            ),
            'is_publicly_hidden' => 0,
            'is_privately_hidden' => 0,
            'taxonomy' => 'organization',
            'add_term' => 0,
            'save_terms' => 0,
            'load_terms' => 0,
            'return_format' => 'id',
            'field_type' => 'multi_select',
            'allow_null' => 0,
            'allow_in_bindings' => 0,
            'bidirectional' => 0,
            'multiple' => 0,
            'bidirectional_target' => array(
            ),
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
    'display_title' => '',
    'allow_ai_access' => false,
    'ai_description' => '',
));
}