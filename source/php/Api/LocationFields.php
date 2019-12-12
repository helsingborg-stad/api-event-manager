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
        add_filter('rest_location_query', array($this, 'addCoordinateFilter'), 10, 2);
    }

    public function registerRestRoute()
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
     * Filter by coordinates and distance
     * @param  array           $args    The query arguments.
     * @param  WP_REST_Request $request Full details about the request.
     * @return array $args.
     **/
    public function addCoordinateFilter($args, $request)
    {
        if (empty($request['latlng'])) {
            return $args;
        }

        $distance = !empty($request['distance']) ? str_replace(',', '.', $request['distance']) : 0;
        $filter = $request['latlng'];
        $latlng = explode(',', preg_replace('/\s+/', '', urldecode($filter)));

        if (count($latlng) != 2) {
            return $args;
        }
        $locations = \HbgEventImporter\Helper\Address::getNearbyLocations($latlng[0], $latlng[1], floatval($distance));
        $idArray = ($locations) ? array_column($locations, 'post_id') : array(0);
        $args['post__in'] = $idArray;

        return $args;
    }

    /**
     * Returning formatted and sorted open hours array
     * @return  array, null
     */
    public function openHoursGetCallBack($object, $field_name, $request, $formatted = true)
    {
        $return_value = self::getFieldGetMetaData($object, $field_name, $request);

        if (is_array($return_value) || is_object($return_value) && !empty($return_value)) {
            $values = $return_value;
        } else {
            return null;
        }

        $days = array(
            1 => 'Monday',
            2 => 'Tuesday',
            3 => 'Wednesday',
            4 => 'Thursday',
            5 => 'Friday',
            6 => 'Saturday',
            7 => 'Sunday'
        );

        // Sort by days order
        usort($values, array($this, 'compareDays'));

        $open_hours = array();
        foreach ($values as $v) {
            $open_hours[] = array(
                            "day_number" => $v['weekday'],
                            "weekday"    => $days[$v['weekday']],
                            "closed"     => $v['closed'],
                            "opening"    => ($v['closed']) ? null : $v['opening'],
                            "closing"    => ($v['closed']) ? null : $v['closing'],
                            );
        }

        return $open_hours;
    }

    /**
     * Usort comparer for weekdays
     */
    public function compareDays($a, $b)
    {
        if ($a['weekday'] == $b['weekday']) {
            return 0;
        }
        return ($a['weekday'] < $b['weekday']) ? -1 : 1;
    }

    /**
     * Get value of latitude (auto or manual)
     * @return string Decimal value of latitude location
     */
    public function getLatitude($object)
    {
        if (get_field('manual_coordinates', $object['id'])) {
            return str_replace(",", ".", get_field('manual_latitude', $object['id']));
        }
        return get_field('latitude', $object['id']);
    }

    /**
     * Get value of longitude (auto or manual)
     * @return string Decimal value of longitude location
     */
    public function getLongitude($object)
    {
        if (get_field('manual_coordinates', $object['id'])) {
            return str_replace(",", ".", get_field('manual_longitude', $object['id']));
        }
        return get_field('longitude', $object['id']);
    }

    /**
     * Register rest fields to consumer api
     * @return  void
     * @version 0.3.2 creating consumer accessable meta values.
     */
    public function registerRestFields()
    {
        // Title as plain text
        register_rest_field($this->postType,
            'title',
            array(
                'get_callback'    => array($this, 'addPlaintextField'),
                'update_callback' => null,
                'schema'          => null,
            )
        );

        //Parent
        register_rest_field($this->postType,
            'parent',
            array(
                'get_callback' => null,
                'update_callback' => null,
                'schema' => array(
                    'description' => 'Field containing value with post parent.',
                    'type' => null,
                    'context' => array('view', 'edit', 'embed')
                )
            )
        );

        // Replace category id with taxonomy name
        register_rest_field($this->postType,
            'location_categories',
            array(
                'get_callback' => array($this, 'renameTaxonomies'),
                'update_callback' => null,
                'schema' => null
            )
        );

        // Replace group id with taxonomy name
        register_rest_field($this->postType,
            'user_groups',
            array(
                'get_callback' => array($this, 'getTaxonomyCallback'),
                'update_callback' => array($this, 'objectUpdateCallBack'),
                'schema' => null
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
                'get_callback' => array($this, 'getLatitude'),
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
                'get_callback' => array($this, 'getLongitude'),
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
            'open_hours',
            array(
                'get_callback' => array($this, 'openHoursGetCallBack'),
                'update_callback' => array($this, 'objectUpdateCallBack'),
                'schema' => array(
                    'description' => 'Field containing object with open hours.',
                    'type' => 'object',
                    'context' => array('view', 'edit', 'embed')
                )
            )
        );

        register_rest_field($this->postType,
            'open_hour_exceptions',
            array(
                'get_callback' => array($this, 'objectGetCallBack'),
                'update_callback' => array($this, 'objectUpdateCallBack'),
                'schema' => array(
                    'description' => 'Field containing object with open hour exceptions.',
                    'type' => 'object',
                    'context' => array('view', 'edit', 'embed')
                )
            )
        );

        // Links
        register_rest_field($this->postType,
            'links',
            array(
                'get_callback' => array($this, 'objectGetCallBack'),
                'update_callback' => array($this, 'objectUpdateCallBack'),
                'schema' => array(
                    'description' => 'Field containing object with links.',
                    'type' => 'object',
                    'context' => array('view', 'edit', 'embed')
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
                    'context' => array('view', 'edit', 'embed')
                )
            )
        );

        // Age restriction details
        register_rest_field($this->postType,
            'age_restriction',
            array(
                'get_callback' => array($this, 'stringGetCallBack'),
                'update_callback' => array($this, 'stringUpdateCallBack'),
                'schema' => array(
                    'description' => 'Field containing string with age restriction details.',
                    'type' => 'string',
                    'context' => array('view', 'edit')
                )
            )
        );

        // Included in member cards
        register_rest_field($this->postType,
            'membership_cards',
            array(
                'get_callback' => array($this, 'objectGetCallBack'),
                'update_callback' => array($this, 'objectGetCallBack'),
                'schema' => array(
                    'description' => 'Field containing array with membership cards.',
                    'type' => 'object',
                    'context' => array('view', 'edit', 'embed')
                )
            )
        );

        //Price details
        register_rest_field($this->postType,
            'price_information',
            array(
                'get_callback' => array($this, 'unformattedStringGetCallBack'),
                'update_callback' => array($this, 'stringUpdateCallBack'),
                'schema' => array(
                    'description' => 'Field containing string with price details.',
                    'type' => 'string',
                    'context' => array('view', 'edit')
                )
            )
        );

        //Price details
        register_rest_field($this->postType,
            'price_adult',
            array(
                'get_callback' => array($this, 'stringGetCallBack'),
                'update_callback' => array($this, 'stringUpdateCallBack'),
                'schema' => array(
                    'description' => 'Field containing string with price details.',
                    'type' => 'string',
                    'context' => array('view', 'edit')
                )
            )
        );

        //Price details
        register_rest_field($this->postType,
            'price_children',
            array(
                'get_callback' => array($this, 'stringGetCallBack'),
                'update_callback' => array($this, 'stringUpdateCallBack'),
                'schema' => array(
                    'description' => 'Field containing string with price details.',
                    'type' => 'string',
                    'context' => array('view', 'edit')
                )
            )
        );

        //Price details
        register_rest_field($this->postType,
            'children_age',
            array(
                'get_callback' => array($this, 'stringGetCallBack'),
                'update_callback' => array($this, 'stringUpdateCallBack'),
                'schema' => array(
                    'description' => 'Field containing string with age restrictions.',
                    'type' => 'string',
                    'context' => array('view', 'edit')
                )
            )
        );

        //Price details
        register_rest_field($this->postType,
            'price_student',
            array(
                'get_callback' => array($this, 'stringGetCallBack'),
                'update_callback' => array($this, 'stringUpdateCallBack'),
                'schema' => array(
                    'description' => 'Field containing string with price details.',
                    'type' => 'string',
                    'context' => array('view', 'edit')
                )
            )
        );

        //Price details
        register_rest_field($this->postType,
            'price_senior',
            array(
                'get_callback' => array($this, 'stringGetCallBack'),
                'update_callback' => array($this, 'stringUpdateCallBack'),
                'schema' => array(
                    'description' => 'Field containing string with price details.',
                    'type' => 'string',
                    'context' => array('view', 'edit')
                )
            )
        );

        //Price details
        register_rest_field($this->postType,
            'senior_age',
            array(
                'get_callback' => array($this, 'numericGetCallBack'),
                'update_callback' => array($this, 'numericUpdateCallBack'),
                'schema' => array(
                    'description' => 'Field containing numeric with age restrictions.',
                    'type' => 'numeric',
                    'context' => array('view', 'edit')
                )
            )
        );

        // Organizer
        register_rest_field($this->postType,
            'organizers',
            array(
                'get_callback' => array($this, 'numericGetCallBack'),
                'update_callback' => array($this, 'numericUpdateCallBack'),
                'schema' => array(
                    'description' => 'Field containing numeric with organizers.',
                    'type' => 'numeric',
                    'context' => array('view', 'edit', 'embed')
                )
            )
        );
    }
}
