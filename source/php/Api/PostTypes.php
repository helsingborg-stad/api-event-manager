<?php

namespace HbgEventImporter\Api;

/**
 * Managing cpt capabilities WordPress API
 */

class PostTypes
{
    public function __construct()
    {
        add_action('init', array($this, 'enableRestApi'), 50);
        add_action('init', array($this, 'disableRestApi'), 50);
    }

    public function enableRestApi()
    {
        global $wp_post_types;

        $post_types = array('event','contact','location');

        if (is_array($post_types) && !empty($post_types)) {
            foreach ($post_types as $post_type) {
                if (isset($wp_post_types[ $post_type ]) && is_object($wp_post_types[ $post_type ])) {
                    $wp_post_types[ $post_type ]->show_in_rest = true;
                    $wp_post_types[ $post_type ]->rest_base = $post_type;
                    $wp_post_types[ $post_type ]->rest_controller_class = 'WP_REST_Posts_Controller';
                }
            }
        }
    }

    public function disableRestApi()
    {
        global $wp_post_types;

        $post_types = array('post','page');

        if (is_array($post_types) && !empty($post_types)) {
            foreach ($post_types as $post_type) {
                if (isset($wp_post_types[ $post_type ]) && is_object($wp_post_types[ $post_type ])) {
                    $wp_post_types[ $post_type ]->show_in_rest = false;
                }
            }
        }
    }
}
