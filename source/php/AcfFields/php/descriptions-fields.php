<?php 

if (function_exists('acf_add_local_field_group')) {
    acf_add_local_field_group(array(
    'key' => 'group_66436bf782af1',
    'title' => __('Description', 'api-event-manager'),
    'fields' => array(
        0 => array(
            'key' => 'field_65a6206610d45',
            'label' => __('Describe your event', 'api-event-manager'),
            'name' => 'description',
            'aria-label' => '',
            'type' => 'textarea',
            'instructions' => __('Describe your event in an enticing way and make it clear what the event is about. Add important information for attendees to know. Include links for details if available. If you are using tags for an event series, enter them here.', 'api-event-manager'),
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
            'acfe_textarea_code' => 0,
            'maxlength' => '',
            'rows' => '',
            'placeholder' => '',
            'new_lines' => '',
        ),
        1 => array(
            'key' => 'field_66106dc161830',
            'label' => __('Add image', 'api-event-manager'),
            'name' => 'image',
            'aria-label' => '',
            'type' => 'image',
            'instructions' => __('Remember that the image uploaded can be cropped. Make sure your images are in landscape (horizontal) format. Avoid text in the images for increased accessibility.

Please note that images with recognizable people are not accepted and will be replaced. Also make sure you have rights to use and distribute the images.', 'api-event-manager'),
            'required' => 1,
            'conditional_logic' => 0,
            'wrapper' => array(
                'width' => '',
                'class' => '',
                'id' => '',
            ),
            'is_publicly_hidden' => 0,
            'is_privately_hidden' => 1,
            'uploader' => '',
            'return_format' => 'array',
            'acfe_thumbnail' => 1,
            'min_width' => '',
            'min_height' => '',
            'min_size' => '',
            'max_width' => '',
            'max_height' => '',
            'max_size' => '',
            'mime_types' => 'jpg, jpeg',
            'preview_size' => 'medium',
            'library' => 'all',
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