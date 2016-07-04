<?php

namespace HbgEventImporter\Api;

/**
 * Filtering WordPress API
 */

class Filter
{

    private $removeFields = array('guid', 'date_gmt', 'modified_tz', 'modified_gmt', 'author', 'link', 'comment_status', 'ping_status', 'sticky', 'format');

    public function __construct()
    {
        //Actions
        add_action('init', array($this, 'redirectToApi'));

        //Filters
        add_filter('rest_url_prefix', array($this, 'apiBasePrefix'), 5000, 1);
        add_filter('rest_prepare_post', array($this, 'removeMetaData'), 10, 3);
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
        if (!is_admin() && strpos($this->currentUrl(), rest_url()) === false && strpos($this->currentUrl(), "wp-admin") === false) {
            wp_redirect(rest_url());
            exit;
        }
    }

    /**
     * Remove fields uncessesary to the applications
     * @return std-object
     */
    public function removeMetaData($response, $post, $context)
    {
        if (is_wp_error($response)) {
            return $response;
        }

        foreach ($this->removeFields as $field) {
            if (isset($response->data[$field])) {
                unset($response->data[$field]);
            }
        }

        return $response;
    }

    public function currentUrl()
    {
        $currentURL = (@$_SERVER["HTTPS"] == "on") ? "https://" : "http://";
        $currentURL .= $_SERVER["SERVER_NAME"];

        if ($_SERVER["SERVER_PORT"] != "80" && $_SERVER["SERVER_PORT"] != "443") {
            $currentURL .= ":".$_SERVER["SERVER_PORT"];
        }

        $currentURL .= $_SERVER["REQUEST_URI"];

        return $currentURL;
    }
}
