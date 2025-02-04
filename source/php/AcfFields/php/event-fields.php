<?php 


if (function_exists('acf_add_local_field_group')) {

    acf_add_local_field_group(array(
    'key' => 'group_65a115157a046',
    'title' => __('General', 'api-event-manager'),
    'fields' => array(
        0 => array(
            'key' => 'field_65a5319a9d01d',
            'label' => __('Status', 'api-event-manager'),
            'name' => 'eventStatus',
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
            'is_publicly_hidden' => 1,
            'is_privately_hidden' => 0,
            'choices' => array(
                'https://schema.org/EventScheduled' => __('Schemalagt', 'api-event-manager'),
                'https://schema.org/EventRescheduled' => __('Omplanerat', 'api-event-manager'),
                'https://schema.org/EventCancelled' => __('Inställt', 'api-event-manager'),
                'https://schema.org/EventPostponed' => __('Framskjutet', 'api-event-manager'),
            ),
            'default_value' => __('https://schema.org/EventScheduled', 'api-event-manager'),
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
    'location' => array(
        0 => array(
            0 => array(
                'param' => 'post_type',
                'operator' => '==',
                'value' => 'event',
            ),
        ),
    ),
    'menu_order' => 0,
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
        5 => 'format',
        6 => 'categories',
        7 => 'tags',
        8 => 'send-trackbacks',
    ),
    'show_in_rest' => 1,
    'description' => 'We are nearly there! Just let us know some important last minute details, and you are ready to go!',
    'show_in_rest' => 0,
    'acfe_display_title' => '',
    'acfe_autosync' => '',
    'acfe_form' => 0,
    'acfe_meta' => '',
    'acfe_note' => '',
));

}