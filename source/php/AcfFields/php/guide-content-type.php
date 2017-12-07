<?php 

if (function_exists('acf_add_local_field_group')) {
    acf_add_local_field_group(array(
    'key' => 'group_5a2940e03054a',
    'title' => __('Type of guide', 'event-manager'),
    'fields' => array(
        0 => array(
            'key' => 'field_5a2940e52d4c8',
            'label' => __('Select content type', 'event-manager'),
            'name' => 'content_type',
            'type' => 'select',
            'instructions' => __('Select trail for wider areas or guide for more narrow areas.', 'event-manager'),
            'required' => 0,
            'conditional_logic' => 0,
            'wrapper' => array(
                'width' => '',
                'class' => '',
                'id' => '',
            ),
            'choices' => array(
                'guide' => __('Guide', 'event-manager'),
                'trail' => __('Trail', 'event-manager'),
            ),
            'default_value' => array(
                0 => 'guide',
            ),
            'allow_null' => 0,
            'multiple' => 0,
            'ui' => 0,
            'ajax' => 0,
            'return_format' => 'value',
            'placeholder' => '',
        ),
    ),
    'location' => array(
        0 => array(
            0 => array(
                'param' => 'post_type',
                'operator' => '==',
                'value' => 'guide',
            ),
        ),
    ),
    'menu_order' => 0,
    'position' => 'side',
    'style' => 'default',
    'label_placement' => 'top',
    'instruction_placement' => 'label',
    'hide_on_screen' => '',
    'active' => 1,
    'description' => '',
));
}