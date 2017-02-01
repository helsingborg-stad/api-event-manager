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
        add_action('rest_api_init', array($this, 'registerRestRoute'));
    }

    public static function registerRestRoute()
    {
        $response = register_rest_route('wp/v2', '/'.$this->postType.'/'.'complete', array(
            'methods'  => \WP_REST_Server::READABLE,
            'callback' => array($this, 'getAllLocations'),
        ));
    }

    /**
     * End point to get all locations, with id and title
     * @return WP_REST_Response / WP_Error
     */
    public function getAllLocations($request)
    {
        global $wpdb;

        $post_status = 'publish';
        $query =
        "
        SELECT      ID as id, post_title as title
        FROM        $wpdb->posts
        WHERE       $wpdb->posts.post_type = %s
                    AND $wpdb->posts.post_status = %s
        ORDER BY post_title ASC
        ";

        $completeQuery = $wpdb->prepare($query, $this->postType, $post_status);
        $allLocations = $wpdb->get_results($completeQuery);

        if (empty($allLocations)) {
            return new \WP_Error('Error', 'There are no locations', array( 'status' => 404 ));
        } else {
            return new \WP_REST_Response($allLocations, 200);
        }
    }

    /**
     * Register rest fields to consumer api
     * @return  void
     * @version 0.3.2 creating consumer accessable meta values.
     */
    public static function registerRestFields()
    {

        // Replace category id with taxonomy name
        register_rest_field($this->postType,
            'location_categories',
            array(
                'get_callback' => array($this, 'renameTaxonomies'),
                'schema' => array(
                    'description' => 'Field containing object with taxonomies.',
                    'type' => 'object',
                    'context' => array('view')
                )
            )
        );

        // Replace group id with taxonomy name
        register_rest_field($this->postType,
            'user_groups',
            array(
                'get_callback' => array($this, 'userGroups'),
                'schema' => array(
                    'description' => 'Field containing object with taxonomies.',
                    'type' => 'object',
                    'context' => array('view')
                )
            )
        );

        //Street adress
        register_rest_field($this->postType,
            'street_address',
            array(
                'get_callback' => array($this, 'stringGetCallBack'),
                'update_callback' => array($this, 'stringUpdateCallBack'),
                'schema' => array(
                    'description' => 'Field containing string value with street adress.',
                    'type' => 'string',
                    'context' => array('view', 'edit', 'embed')
                )
            )
        );

        //Postal code
        register_rest_field($this->postType,
            'postal_code',
            array(
                'get_callback' => array($this, 'stringGetCallBack'),
                'update_callback' => array($this, 'stringUpdateCallBack'),
                'schema' => array(
                    'description' => 'Field containing string value with postal code.',
                    'type' => 'string',
                    'context' => array('view', 'edit', 'embed')
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
                    'description' => 'Field containing string value with city.',
                    'type' => 'string',
                    'context' => array('view', 'edit', 'embed')
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
                    'description' => 'Field containing string value with municipality.',
                    'type' => 'string',
                    'context' => array('view', 'edit', 'embed')
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
                    'description' => 'Field containing string value with country.',
                    'type' => 'string',
                    'context' => array('view', 'edit', 'embed')
                )
            )
        );

        //Latitude
        register_rest_field($this->postType,
            'latitude',
            array(
                'get_callback' => array($this, 'stringGetCallBack'),
                'update_callback' => array($this, 'stringUpdateCallBack'),
                'schema' => array(
                    'description' => 'Field containing string value with coordinates.',
                    'type' => 'string',
                    'context' => array('view', 'edit', 'embed')
                )
            )
        );

        //Latitude
        register_rest_field($this->postType,
            'longitude',
            array(
                'get_callback' => array($this, 'stringGetCallBack'),
                'update_callback' => array($this, 'stringUpdateCallBack'),
                'schema' => array(
                    'description' => 'Field containing string value with coordinates.',
                    'type' => 'string',
                    'context' => array('view', 'edit', 'embed')
                )
            )
        );

        //Formatted address
        register_rest_field($this->postType,
            'formatted_address',
            array(
                'get_callback' => array($this, 'stringGetCallBack'),
                'update_callback' => array($this, 'stringUpdateCallBack'),
                'schema' => array(
                    'description' => 'Field containing string value with formatted address.',
                    'type' => 'string',
                    'context' => array('view', 'edit', 'embed')
                )
            )
        );

        //Open hours
        register_rest_field($this->postType,
            'oph_exceptions',
            array(
                'get_callback' => array($this, 'objectGetCallBack'),
                'update_callback' => array($this, 'objectUpdateCallBack'),
                'schema' => array(
                    'description' => 'Field containing object with open hour exceptions.',
                    'type' => 'object',
                    'context' => array('view', 'edit')
                )
            )
        );

        register_rest_field($this->postType,
            'open_hours_mon',
            array(
                'get_callback' => array($this, 'stringGetCallBack'),
                'update_callback' => array($this, 'stringUpdateCallBack'),
                'schema' => array(
                    'description' => 'Field containing object with open hours.',
                    'type' => 'string',
                    'context' => array('view', 'edit')
                )
            )
        );

        register_rest_field($this->postType,
            'open_hours_tue',
            array(
                'get_callback' => array($this, 'stringGetCallBack'),
                'update_callback' => array($this, 'stringUpdateCallBack'),
                'schema' => array(
                    'description' => 'Field containing object with open hours.',
                    'type' => 'string',
                    'context' => array('view', 'edit')
                )
            )
        );

        register_rest_field($this->postType,
            'open_hours_wed',
            array(
                'get_callback' => array($this, 'stringGetCallBack'),
                'update_callback' => array($this, 'stringUpdateCallBack'),
                'schema' => array(
                    'description' => 'Field containing object with open hours.',
                    'type' => 'string',
                    'context' => array('view', 'edit')
                )
            )
        );

        register_rest_field($this->postType,
            'open_hours_thu',
            array(
                'get_callback' => array($this, 'stringGetCallBack'),
                'update_callback' => array($this, 'stringUpdateCallBack'),
                'schema' => array(
                    'description' => 'Field containing object with open hours.',
                    'type' => 'string',
                    'context' => array('view', 'edit')
                )
            )
        );

        register_rest_field($this->postType,
            'open_hours_fri',
            array(
                'get_callback' => array($this, 'stringGetCallBack'),
                'update_callback' => array($this, 'stringUpdateCallBack'),
                'schema' => array(
                    'description' => 'Field containing object with open hours.',
                    'type' => 'string',
                    'context' => array('view', 'edit')
                )
            )
        );

        register_rest_field($this->postType,
            'open_hours_sat',
            array(
                'get_callback' => array($this, 'stringGetCallBack'),
                'update_callback' => array($this, 'stringUpdateCallBack'),
                'schema' => array(
                    'description' => 'Field containing object with open hours.',
                    'type' => 'string',
                    'context' => array('view', 'edit')
                )
            )
        );

        register_rest_field($this->postType,
            'open_hours_sun',
            array(
                'get_callback' => array($this, 'stringGetCallBack'),
                'update_callback' => array($this, 'stringUpdateCallBack'),
                'schema' => array(
                    'description' => 'Field containing object with open hours.',
                    'type' => 'string',
                    'context' => array('view', 'edit')
                )
            )
        );

        // Gallery
        register_rest_field($this->postType,
            'gallery',
            array(
                'get_callback' => array($this, 'objectGetCallBack'),
                'update_callback' => array($this, 'objectUpdateCallBack'),
                'schema' => array(
                    'description' => 'Field containing object with gallery.',
                    'type' => 'object',
                    'context' => array('view', 'edit')
                )
            )
        );
    }
}
