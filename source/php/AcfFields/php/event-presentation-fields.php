<?php 

if (function_exists('acf_add_local_field_group')) {
    acf_add_local_field_group(array(
    'key' => 'group_68d28e8bc6408',
    'title' => __('Presentation', 'api-event-manager'),
    'fields' => array(
        0 => array(
            'key' => 'field_68d28e8bc7a4e',
            'label' => __('Images', 'api-event-manager'),
            'name' => 'images',
            'aria-label' => '',
            'type' => 'gallery',
            'instructions' => __('Make your event stand out by uploading high-resolution images. Your event image is often the first thing attendees notice, so choose visuals that capture attention and spark interest. Images will appear in the order they are uploaded.', 'api-event-manager'),
            'required' => 1,
            'conditional_logic' => 0,
            'wrapper' => array(
                'width' => '',
                'class' => '',
                'id' => '',
            ),
            'is_publicly_hidden' => 0,
            'is_privately_hidden' => 0,
            'return_format' => 'id',
            'library' => 'all',
            'min' => 1,
            'max' => 4,
            'min_width' => '',
            'min_height' => '',
            'min_size' => '',
            'max_width' => '',
            'max_height' => '',
            'max_size' => '',
            'mime_types' => '',
            'insert' => 'append',
            'preview_size' => 'medium',
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
    'menu_order' => 2,
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
        5 => 'slug',
        6 => 'format',
        7 => 'page_attributes',
        8 => 'featured_image',
        9 => 'categories',
        10 => 'tags',
        11 => 'send-trackbacks',
    ),
    'active' => true,
    'description' => '',
    'show_in_rest' => 1,
    'display_title' => '',
));
}