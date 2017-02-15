<?php

namespace HbgEventImporter\Acf;

/**
 * Load local ACF field groups with PHP
 */

class AcfGuideSettings
{
    public function __construct()
    {
        //add_action('acf/init', array($this, 'addLocalFieldGroups'));
    }

    public function addLocalFieldGroups()
    {
        if (function_exists('acf_add_local_field_group')):

        acf_add_local_field_group(array(
            'key' => 'group_589dcf7e047a8',
            'title' => __('Guide apperance settings', 'event-manager'),
            'fields' => array(
                array(
                    'return_format' => 'array',
                    'preview_size' => 'medium',
                    'library' => 'uploadedTo',
                    'min_width' => '',
                    'min_height' => '',
                    'min_size' => '',
                    'max_width' => '',
                    'max_height' => '',
                    'max_size' => '',
                    'mime_types' => 'svg, png',
                    'key' => 'field_589dcf9661090',
                    'label' => __('Logotype', 'event-manager'),
                    'name' => 'guide_taxonomy_logotype',
                    'type' => 'image',
                    'instructions' => __('A logotype that may be shown related to the guides with this category.', 'event-manager'),
                    'required' => 0,
                    'conditional_logic' => 0,
                    'wrapper' => array(
                        'width' => '',
                        'class' => '',
                        'id' => '',
                    ),
                ),
                array(
                    'default_value' => '000000',
                    'maxlength' => '',
                    'placeholder' => '',
                    'prepend' => '',
                    'append' => '',
                    'key' => 'field_589dd18acd51f',
                    'label' => __('Color', 'event-manager'),
                    'name' => 'guide_taxonomy_color',
                    'type' => 'text',
                    'instructions' => '',
                    'required' => 0,
                    'conditional_logic' => 0,
                    'wrapper' => array(
                        'width' => '',
                        'class' => 'colorpicker',
                        'id' => '',
                    ),
                ),
                array(
                    'return_format' => 'array',
                    'preview_size' => 'thumbnail',
                    'library' => 'all',
                    'min_width' => '',
                    'min_height' => '',
                    'min_size' => '',
                    'max_width' => '',
                    'max_height' => '',
                    'max_size' => '',
                    'mime_types' => 'png, jpg, jpeg',
                    'key' => 'field_58a2d24ca466c',
                    'label' => __('Mood image', 'event-manager'),
                    'name' => 'guide_taxonomy_image',
                    'type' => 'image',
                    'instructions' => '',
                    'required' => 0,
                    'conditional_logic' => 0,
                    'wrapper' => array(
                        'width' => '',
                        'class' => '',
                        'id' => '',
                    ),
                ),
            ),
            'location' => array(
                array(
                    array(
                        'param' => 'taxonomy',
                        'operator' => '==',
                        'value' => 'guide_sender',
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

        endif;
    }
}
