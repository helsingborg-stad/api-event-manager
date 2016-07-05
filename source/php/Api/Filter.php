<?php

namespace HbgEventImporter\Api;

/**
 * Filtering WordPress API
 */

class Filter
{

    private $removeFields;

    public function __construct()
    {
        //Actions
        add_action('init', array($this, 'redirectToApi'));

        //Filters
        add_filter('rest_url_prefix', array($this, 'apiBasePrefix'), 5000, 1);
        //add_filter('rest_prepare_post', array($this, 'removeResponseData'), 100000, 3);


        add_action('rest_api_init', array($this,'apiAddFields'));

    }

    /**
     * Rename /wp-json/ to /json/.
     * @return string Returning an empty string.
     */
    public function apiBasePrefix($prefix)
    {
        return "json";
    }

    /**
     * Force the usage of wordpress api
     * @return void
     */
    public function redirectToApi()
    {
        if (!is_admin() && strpos($this->currentUrl(), rtrim(rest_url(),"/")) === false && $this->currentUrl() == rtrim(home_url(),"/")) {
            wp_redirect(rest_url());
            exit;
        }
    }

    public function currentUrl()
    {
        $currentURL = (@$_SERVER["HTTPS"] == "on") ? "https://" : "http://";
        $currentURL .= $_SERVER["SERVER_NAME"];

        if ($_SERVER["SERVER_PORT"] != "80" && $_SERVER["SERVER_PORT"] != "443") {
            $currentURL .= ":".$_SERVER["SERVER_PORT"];
        }

        $currentURL .= $_SERVER["REQUEST_URI"];

        return rtrim($currentURL,"/");
    }

    public function apiAddFields() {
        register_rest_field('location',
            'postal_code',
            array(
                'get_callback' => function($post, $field_name, $request){
                    return get_post_meta($post->id, $field_name);
                },
                'update_callback' => function($value, $post, $field_name){
                    global $post;
                    if (!$value || !is_string($value)) {
                        return;
                    }
                    return update_post_meta($post->ID, $field_name, strip_tags($value));
                },
                'schema' => array(
                                    'description' => 'My special field',
                                    'type' => 'string',
                                    'context' => array('view', 'edit')
                                )
            )
        );
    }
}
