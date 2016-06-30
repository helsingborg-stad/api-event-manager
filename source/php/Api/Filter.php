<?php

namespace HbgEventImporter\Api;

/**
 * Filtering WordPress API
 */

class Filter
{
    public function __construct()
    {
        add_filter('json_url_prefix', array($this, 'apiBasePrefix'));
        add_filter('json_endpoints', array($this, 'translateDefaultRoutes'));
    }

    /**
     * Rename /wp-json/ to /api/.
     * @return string Returning an empty string.
     */
    public function apiBasePrefix()
    {
        return "api";
    }

    public function translateDefaultRoutes($routes)
    {
        return $routes;
    }
}
