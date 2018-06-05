<?php 

if (function_exists('acf_add_local_field_group')) {
    acf_add_local_field_group(array(
    'key' => 'group_5a27f69d7c9cc',
    'title' => __('Include in category', 'event-manager'),
    'fields' => array(
        0 => array(
            'key' => 'field_5a292d1f8d9ec',
            'label' => __('Select specific items (posts)', 'event-manager'),
            'name' => 'include_specific_posts',
            'type' => 'true_false',
            'instructions' => __('To select none, please switch to "select specific items" and leave specific items field blank.', 'event-manager'),
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
            'ui_on_text' => __('Select specific items', 'event-manager'),
            'ui_off_text' => __('Include all items', 'event-manager'),
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
                        'field' => 'field_5a292d1f8d9ec',
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
            'key' => 'field_5a292cbd8d9eb',
            'label' => __('Select specific items (taxonomy)', 'event-manager'),
            'name' => 'include_specific_taxonomys',
            'type' => 'true_false',
            'instructions' => __('To select none, please switch to "select specific items" and leave specific items field blank.', 'event-manager'),
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
            'ui_on_text' => __('Select specific items', 'event-manager'),
            'ui_off_text' => __('Include all items', 'event-manager'),
        ),
        3 => array(
            'key' => 'field_5a27fed65a89d',
            'label' => __('Include these items (taxonomy)', 'event-manager'),
            'name' => 'included_taxonomys',
            'type' => 'taxonomy',
            'instructions' => '',
            'required' => 0,
            'conditional_logic' => array(
                0 => array(
                    0 => array(
                        'field' => 'field_5a292cbd8d9eb',
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
            'taxonomy' => 'guidegroup',
            'field_type' => 'multi_select',
            'allow_null' => 0,
            'add_term' => 0,
            'save_terms' => 0,
            'load_terms' => 0,
            'return_format' => 'object',
            'multiple' => 0,
        ),
        4 => array(
            'key' => 'field_5b165c92514b8',
            'label' => __('Select specific recommendations', 'event-manager'),
            'name' => 'include_specific_recommendations',
            'type' => 'true_false',
            'instructions' => __('To select none, please switch to "select specific items" and leave specific items field blank.', 'event-manager'),
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
            'ui_on_text' => __('Select specific items', 'event-manager'),
            'ui_off_text' => __('Include all items', 'event-manager'),
        ),
        5 => array(
            'key' => 'field_5b165c91514b7',
            'label' => __('Include these recommendations', 'event-manager'),
            'name' => 'included_recommendations',
            'type' => 'post_object',
            'instructions' => '',
            'required' => 0,
            'conditional_logic' => array(
                0 => array(
                    0 => array(
                        'field' => 'field_5b165c92514b8',
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
            'post_type' => array(
                0 => 'recommendation',
            ),
            'taxonomy' => array(
            ),
            'allow_null' => 0,
            'multiple' => 1,
            'return_format' => 'object',
            'ui' => 1,
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