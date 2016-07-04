<?php

namespace HbgEventImporter\Api;

/**
 * Filtering WordPress API
 */

class Filter
{
    public function __construct()
    {
        //add_filter('rest_url_prefix', array($this, 'apiBasePrefix'), 5000, 1);
        add_filter('rest_endpoints', array($this, 'translateDefaultRoutes'));
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

    public function translateDefaultRoutes($routes)
    {
        $new_routes["/"] = $routes["/"];
        print_r($new_routes);
        return $new_routes;
    }

    public function removeMetaData($data, $post, $context)
    {
        if ($context !== 'view' || is_wp_error($data)) {
            return $data;
        }

        unset($data['date_gmt']);
        unset($data['modified_tz']);
        unset($data['modified_gmt']);
        unset($data['author']);

        return $data;
    }
}
