<?php

if (function_exists('acf_add_local_field_group')) {
    acf_add_local_field_group(array(
    'key'                   => 'group_65ae1b865887a',
    'title'                 => __('Audience Fields', 'api-event-manager'),
    'fields'                => array(
        0 => array(
            'key'               => 'field_65ae1b863fa54',
            'label'             => __('Typical age range start', 'api-event-manager'),
            'name'              => 'typicalAgeRangeStart',
            'aria-label'        => '',
            'type'              => 'number',
            'instructions'      => '',
            'required'          => 0,
            'conditional_logic' => 0,
            'wrapper'           => array(
                'width' => '',
                'class' => '',
                'id'    => '',
            ),
            'default_value'     => '',
            'min'               => 0,
            'max'               => '',
            'placeholder'       => '',
            'step'              => '',
            'prepend'           => '',
            'append'            => '',
        ),
        1 => array(
            'key'               => 'field_65ae1bf03fa55',
            'label'             => __('Typical age range end', 'api-event-manager'),
            'name'              => 'typicalAgeRangeEnd',
            'aria-label'        => '',
            'type'              => 'number',
            'instructions'      => '',
            'required'          => 0,
            'conditional_logic' => 0,
            'wrapper'           => array(
                'width' => '',
                'class' => '',
                'id'    => '',
            ),
            'default_value'     => '',
            'min'               => 0,
            'max'               => '',
            'placeholder'       => '',
            'step'              => '',
            'prepend'           => '',
            'append'            => '',
        ),
    ),
    'location'              => array(
        0 => array(
            0 => array(
                'param'    => 'taxonomy',
                'operator' => '==',
                'value'    => 'audience',
            ),
        ),
    ),
    'menu_order'            => 0,
    'position'              => 'normal',
    'style'                 => 'default',
    'label_placement'       => 'top',
    'instruction_placement' => 'label',
    'hide_on_screen'        => '',
    'active'                => true,
    'description'           => '',
    'show_in_rest'          => 0,
    ));
}
