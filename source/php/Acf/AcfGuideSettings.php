<?php

namespace HbgEventImporter\Acf;

/**
 * Load local ACF field groups with PHP
 */

class AcfGuideSettings
{
    public function __construct()
    {
        add_action('acf/init', array($this, 'addLocalFieldGroups'));
    }

    public function addLocalFieldGroups()
    {
        if (function_exists('acf_add_local_field_group')):

        acf_add_local_field_group(array(
            'key' => 'group_589dd0fbd412e',
            'title' => __('Guide apperance', 'event-manager'),
            'fields' => array(
                array(
                    'taxonomy' => 'guide_sender',
                    'field_type' => 'select',
                    'multiple' => 0,
                    'allow_null' => 0,
                    'return_format' => 'object',
                    'add_term' => 1,
                    'load_terms' => 0,
                    'save_terms' => 0,
                    'key' => 'field_589dd138aca7e',
                    'label' => __('Select apperance', 'event-manager'),
                    'name' => 'guide_apperance_data',
                    'type' => 'taxonomy',
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
