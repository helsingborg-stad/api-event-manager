<?php

namespace HbgEventImporter\Api;

/**
 * Adding meta fields to location post type
 */

class EventFields extends Fields
{

    private $postType = 'event';

    public function __construct()
    {
        add_action('rest_api_init', array($this, 'registerRestFields'));
    }

    public static function registerRestFields()
    {

        //Street adress
        register_rest_field($this->postType,
            'street_address',
            array(
                'get_callback' => array($this, 'stringGetCallBack'),
                'update_callback' => array($this, 'stringUpdateCallBack'),
                'schema' => array(
                    'description' => 'Field contianing string value with street adress.',
                    'type' => 'string',
                    'context' => array('view', 'edit')
                )
            )
        );

    }
}
