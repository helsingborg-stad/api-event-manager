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
}
