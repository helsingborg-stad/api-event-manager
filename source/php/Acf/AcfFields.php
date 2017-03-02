<?php

namespace HbgEventImporter\Acf;

/**
 * Load local ACF field groups with PHP
 */

class AcfFields
{
    public function __construct()
    {
        add_action('acf/init', array($this, 'addLocalFieldGroups'));
    }

    public function addLocalFieldGroups()
    {

//TODO: https://www.advancedcustomfields.com/resources/custom-location-rules/

    // Save user groups option to array
    $groups = get_field('event_group_select', 'option');

    $user_groups = array();
    if (is_array($groups) && !empty($groups)) {
        foreach ($groups as $group) {
            $user_groups[] = array(
                    array(
                    'param' => 'post_type',
                    'operator' => '==',
                    'value' => $group,
                ),
            );
        }
    }

     /**
     * User groups
     */
    acf_add_local_field_group(array (
        'key' => 'group_5885f51260b61',
        'title' => __('Groups', 'event-manager'),
        'fields' => array (
            array (
                'key' => 'field_5885f58a36f81',
                'label' => __('Missing user group', 'event-manager'),
                'name' => 'missing_user_group',
                'type' => 'true_false',
                'instructions' => '',
                'required' => 0,
                'conditional_logic' => 0,
                'wrapper' => array (
                    'width' => '',
                    'class' => '',
                    'id' => '',
                ),
                'message' => __('Check this box if the post does not belong to any user groups. This post will be available to all users.', 'event-manager'),
                'default_value' => 0,
            ),
            array (
                'key' => 'field_5885f58c36f82',
                'label' => __('Groups', 'event-manager'),
                'name' => 'user_groups',
                'type' => 'taxonomy',
                'instructions' => __('Select one or many user groups.', 'event-manager'),
                'required' => 1,
                'conditional_logic' => array (
                    array (
                        array (
                            'field' => 'field_5885f58a36f81',
                            'operator' => '!=',
                            'value' => '1',
                        ),
                    ),
                ),
                'wrapper' => array (
                    'width' => '',
                    'class' => '',
                    'id' => '',
                ),
                'taxonomy' => 'user_groups',
                'field_type' => 'checkbox',
                'allow_null' => 0,
                'add_term' => 1,
                'save_terms' => 1,
                'load_terms' => 0,
                'return_format' => 'id',
                'multiple' => 0,
            ),
        ),
        'location' => $user_groups,
        'menu_order' => 1,
        'position' => 'normal',
        'style' => 'default',
        'label_placement' => 'top',
        'instruction_placement' => 'label',
        'hide_on_screen' => '',
        'active' => 1,
        'description' => '',
        ));

    }
}
