<?php 

if (function_exists('acf_add_local_field_group')) {
    acf_add_local_field_group(array(
    'key' => 'group_5a27f69d7c9cc',
    'title' => __('Include in category', 'event-manager'),
    'fields' => array(
        0 => array(
            'key' => 'field_5a27fe465a89c',
            'label' => __('Type of data in category', 'event-manager'),
            'name' => 'type_of_data',
            'type' => 'radio',
            'instructions' => '',
            'required' => 0,
            'conditional_logic' => 0,
            'wrapper' => array(
                'width' => '',
                'class' => '',
                'id' => '',
            ),
            'choices' => array(
                'organisation' => __('Organisation', 'event-manager'),
                'trail' => __('Trails', 'event-manager'),
            ),
            'allow_null' => 0,
            'other_choice' => 0,
            'save_other_choice' => 0,
            'default_value' => 'organisation',
            'layout' => 'horizontal',
            'return_format' => 'value',
        ),
        1 => array(
            'key' => 'field_5a27f6ab57d2f',
            'label' => __('Include these items (posts)', 'event-manager'),
            'name' => 'included_posts',
            'type' => 'post_object',
            'instructions' => '',
            'required' => 0,
            'conditional_logic' => array(
                0 => array(
                    0 => array(
                        'field' => 'field_5a27fe465a89c',
                        'operator' => '==',
                        'value' => 'trail',
                    ),
                ),
            ),
            'wrapper' => array(
                'width' => '',
                'class' => '',
                'id' => '',
            ),
            'post_type' => array(
                0 => 'guide',
            ),
            'taxonomy' => array(
            ),
            'allow_null' => 0,
            'multiple' => 1,
            'return_format' => 'object',
            'ui' => 1,
        ),
        2 => array(
            'key' => 'field_5a27fed65a89d',
            'label' => __('Include these items (taxonomy)', 'event-manager'),
            'name' => 'included_taxonomys',
            'type' => 'taxonomy',
            'instructions' => '',
            'required' => 0,
            'conditional_logic' => array(
                0 => array(
                    0 => array(
                        'field' => 'field_5a27fe465a89c',
                        'operator' => '==',
                        'value' => 'organisation',
                    ),
                ),
            ),
            'wrapper' => array(
                'width' => '',
                'class' => '',
                'id' => '',
            ),
            'taxonomy' => 'guidegroup',
            'field_type' => 'multi_select',
            'allow_null' => 0,
            'add_term' => 0,
            'save_terms' => 0,
            'load_terms' => 0,
            'return_format' => 'object',
            'multiple' => 0,
        ),
        3 => array(
            'key' => 'field_5a28020b09d68',
            'label' => __('About', 'event-manager'),
            'name' => '',
            'type' => 'message',
            'instructions' => '',
            'required' => 0,
            'conditional_logic' => 0,
            'wrapper' => array(
                'width' => '',
                'class' => '',
                'id' => '',
            ),
            'message' => __('<span style="color: #ff0000;">There are not need to add the navigation alternative "all" due to the fact that this item is a combination of all other navigation items. This item will automatically be created. </span>', 'event-manager'),
            'new_lines' => '',
            'esc_html' => 0,
        ),
    ),
    'location' => array(
        0 => array(
            0 => array(
                'param' => 'taxonomy',
                'operator' => '==',
                'value' => 'navigation',
            ),
        ),
    ),
    'menu_order' => 0,
    'position' => 'normal',
    'style' => 'default',
    'label_placement' => 'top',
    'instruction_placement' => 'label',
    'hide_on_screen' => '',
    'active' => 1,
    'description' => '',
));
}