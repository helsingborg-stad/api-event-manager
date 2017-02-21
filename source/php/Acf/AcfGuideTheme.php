<?php

namespace HbgEventImporter\Acf;

/**
 * Load local ACF field groups with PHP
 */

class AcfGuideTheme
{
    public function __construct()
    {
        //add_action('acf/init', array($this, 'addLocalFieldGroups'));
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
                    'required' => 1,
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

        endif;
    }
}
