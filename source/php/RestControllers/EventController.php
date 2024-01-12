<?php

namespace EventManager\RestControllers;

use WP_REST_Posts_Controller;

class EventController extends WP_REST_Posts_Controller
{
    public function get_context_param($args = array()) // phpcs:ignore PSR1.Methods.CamelCapsMethodName.NotCamelCaps
    {
        $context           = parent::get_context_param($args);
        $context['enum'][] = 'schema';
        return $context;
    }
}
