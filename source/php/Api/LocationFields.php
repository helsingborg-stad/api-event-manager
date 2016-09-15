<?php

namespace HbgEventImporter\Api;

/**
 * Adding meta fields to location post type
 */

class LocationFields extends Fields
{
    private $postType = 'location';

    public function __construct()
    {
        add_action('rest_api_init', array($this, 'registerRestFields'));
    }

    /**
     * Register rest fields to consumer api
     * @return  void
     * @version 0.3.2 creating consumer accessable meta values.
     */
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

        //Postal code
        register_rest_field($this->postType,
            'postal_code',
            array(
                'get_callback' => array($this, 'numericGetCallBack'),
                'update_callback' => array($this, 'numericUpdateCallBack'),
                'schema' => array(
                    'description' => 'Field contianing numeric value with postal code.',
                    'type' => 'number',
                    'context' => array('view', 'edit')
                )
            )
        );

         //Street adress
        register_rest_field($this->postType,
            'city',
            array(
                'get_callback' => array($this, 'stringGetCallBack'),
                'update_callback' => array($this, 'stringUpdateCallBack'),
                'schema' => array(
                    'description' => 'Field contianing string value with city.',
                    'type' => 'string',
                    'context' => array('view', 'edit')
                )
            )
        );

        //Municipality
        register_rest_field($this->postType,
            'municipality',
            array(
                'get_callback' => array($this, 'stringGetCallBack'),
                'update_callback' => array($this, 'stringUpdateCallBack'),
                'schema' => array(
                    'description' => 'Field contianing string value with municipality.',
                    'type' => 'string',
                    'context' => array('view', 'edit')
                )
            )
        );

        //Country
        register_rest_field($this->postType,
            'country',
            array(
                'get_callback' => array($this, 'stringGetCallBack'),
                'update_callback' => array($this, 'stringUpdateCallBack'),
                'schema' => array(
                    'description' => 'Field contianing string value with country.',
                    'type' => 'string',
                    'context' => array('view', 'edit')
                )
            )
        );

        //Map data
        register_rest_field($this->postType,
            'map',
            array(
                'get_callback' => array($this, 'objectGetCallBack'),
                'update_callback' => array($this, 'objectUpdateCallBack'),
                'schema' => array(
                    'description' => 'Field contianing string value with map details.',
                    'type' => 'string',
                    'context' => array('view', 'edit')
                )
            )
        );
    }
}
