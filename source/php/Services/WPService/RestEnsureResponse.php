<?php

namespace EventManager\Services\WPService;

use WP_Error;
use WP_REST_Response;
use WP_HTTP_Response;

interface RestEnsureResponse
{
    /**
     * Ensures a REST response is a response object (for consistency).
     *
     * This implements WP_REST_Response, allowing usage of `set_status`/`header`/etc
     * without needing to double-check the object. Will also allow WP_Error to indicate error
     * responses, so users should immediately check for this value.
     *
     * @param WP_REST_Response|WP_Error|WP_HTTP_Response|mixed $response Response to check.
     * @return WP_REST_Response|WP_Error If response generated an error, WP_Error, if response
     *                                   is already an instance, WP_REST_Response, otherwise
     *                                   returns a new WP_REST_Response instance.
    */
    public function restEnsureResponse($response): WP_REST_Response|WP_Error;
}
