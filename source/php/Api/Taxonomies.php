<?php

namespace HbgEventImporter\Api;

/**
 * Managing cpt capabilities WordPress API
 */

class Taxonomies
{
    public function __construct()
    {
        add_action('init', array($this, 'enableTaxonomyRestApi'), 50);
    }

    public function enableTaxonomyRestApi()
    {
        global $wp_taxonomies;

        $taxonomies=  array('event_categories', 'event_tags', 'user_groups', 'location_categories', 'guide_group');

        if (is_array($taxonomies) && !empty($taxonomies)) {
            foreach ($taxonomies as $taxonomy) {
                if (isset($wp_taxonomies[ $taxonomy ]) && is_object($wp_taxonomies[ $taxonomy ])) {
                    $wp_taxonomies[ $taxonomy ]->show_in_rest = true;
                    $wp_taxonomies[ $taxonomy ]->rest_base = $taxonomy;
                    $wp_taxonomies[ $taxonomy ]->rest_controller_class = 'WP_REST_Terms_Controller';
                }
            }
        }
    }
}
