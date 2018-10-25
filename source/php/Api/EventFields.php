<?php

namespace HbgEventImporter\Api;

/**
 * Register custom endpoints and meta fields to event post type
 */

class EventFields extends Fields
{
    private $postType = 'event';

    public function __construct()
    {
        add_action('rest_api_init', array($this, 'registerRestRoute'));
        add_action('rest_api_init', array($this, 'registerRestRouteSearch'));
        add_action('rest_api_init', array($this, 'registerRestFields'));
        add_filter('rest_prepare_event', array($this, 'setCurrentOccasion'), 10, 3);
        add_filter('rest_prepare_event', array($this, 'removeLinksObject'), 200, 3);
    }

    public function registerRestRoute()
    {
        register_rest_route('wp/v2', '/' . $this->postType . '/' . 'time', array(
            'methods' => \WP_REST_Server::READABLE,
            'callback' => array($this, 'getEventsByTimestamp'),
            'args' => $this->getCollectionParams(),
        ));
    }

    /**
     * Remove all linking on time endpoint
     * @return array
     */
    public function removeLinksObject($data, $post, $context)
    {

        //Limit to /time endpoint
        if (!isset($_GET['start'])) {
            return $data;
        }

        //Remove all links
        $data->remove_link('collection');
        $data->remove_link('about');
        $data->remove_link('version-history');
        $data->remove_link('predecessor-version');
        $data->remove_link('organizers');
        $data->remove_link('location');
        $data->remove_link('complete');
        $data->remove_link('gallery');
        $data->remove_link('https://api.w.org/featuredmedia');
        $data->remove_link('https://api.w.org/attachment');
        $data->remove_link('https://api.w.org/term');
        $data->remove_link('curies');

        return $data;
    }


    public function registerRestRouteSearch()
    {
        register_rest_route('wp/v2', '/event/search', array(
            'methods' => \WP_REST_Server::READABLE,
            'callback' => array($this, 'getEventsSearch'),
        ));
    }

    /**
     * Get the query params for collections
     * @return array
     */
    public function getCollectionParams() {
        return array(
            'start'                  => array(
                'description'        => 'Start date for the collection.',
                'type'               => 'integer',
                'default'            => strtotime('midnight now'),
                'sanitize_callback'  => array($this, 'sanitizeDate'),
            ),
            'end'                    => array(
                'description'        => 'End date for the collection.',
                'type'               => 'integer',
                'default'            => strtotime('midnight now'),
                'sanitize_callback'  => array($this, 'sanitizeEndDate'),
            ),
            'internal'               => array(
                'description'        => 'Filter by internal events. Check if only internal (organization) event should be fetched.',
                'type'               => 'integer',
                'default'            => 0,
                'sanitize_callback'  => array($this, 'sanitizeBool'),
            ),
            'latlng'                 => array(
                'description'        => 'Filter by locations at a specified location and inside a circular radius.',
                'type'               => 'string',
                'default'            => '',
                'sanitize_callback'  => 'sanitize_text_field',
            ),
            'distance'               => array(
                'description'        => 'Area distance in km from coordinates. Used with "latlng" parameter.',
                'type'               => 'float',
                'default'            => 0,
                'sanitize_callback'  => array($this, 'sanitizeFloat'),
            ),
            'arealatlng'             => array(
                'description'        => 'Filter by locations inside a polygon shaped area.',
                'type'               => 'array',
                'default'            => array(),
                'sanitize_callback'  => array($this, 'sanitizeArray'),
            ),
            'group-id'               => array(
                'description'        => 'Filter by groups taxonomy.',
                'type'               => 'string',
                'default'            => '',
                'sanitize_callback'  => 'sanitize_text_field',
            ),
            'category-id'            => array(
                'description'        => 'Filter by categories taxonomy.',
                'type'               => 'string',
                'default'            => '',
                'sanitize_callback'  => 'sanitize_text_field',
            ),
            'post-limit'            => array(
                'description'        => 'Filter by categories taxonomy.',
                'type'               => 'integer',
                'default'            => '99',
                'sanitize_callback'  => array($this, 'sanitizeInt'),
            ),
        );
    }

    /**
     * Returns positive int
     * @param $data
     * @return int
     */
    public function sanitizeInt($data)
    {
        return absint($data);
    }

    /**
     * Returns positive int
     * @param $data
     * @return float|int
     */
    public function sanitizePage($data)
    {
        $data = absint($data);
        return ($data >= 1) ? $data : 1;
    }

    /**
     * Return int between 1-100
     * @param $data
     * @return int
     */
    public function sanitizePerPage($data)
    {
        $data = absint($data);
        return ($data >= 1 && $data <= 100) ? $data : 10;
    }

    /**
     * Returns array
     * @param $data
     * @return array
     */
    public function sanitizeArray($data)
    {
        return is_array($data) ? $data : array();
    }

    /**
     * Return float
     * @param $data
     * @return float
     */
    public function sanitizeFloat($data)
    {
        return floatval(str_replace(',', '.', $data));
    }

    /**
     * Return int instead of bool
     * @param $data
     * @return int
     */
    public function sanitizeBool($data)
    {
        return $data ? 1 : 0;
    }

    /**
     * Convert date to timestamp
     * @param $data
     * @return int
     */
    public function sanitizeDate($data)
    {
        if (!is_numeric($data)) {
            $data = strtotime($data);
        }

        if ($data == false) {
            $data = strtotime('midnight now');
        }

        return $data;
    }

    /**
     * Convert end date to timestamp. Add 1 day to include events occurring on end date
     * @param $data
     * @return int
     */
    public function sanitizeEndDate($data)
    {
        return strtotime('+1 day', $this->sanitizeDate($data));
    }

    /**
     * Endpoint to search events by title
     * @param $data
     * @return array|null|object
     */
    public function getEventsSearch($data)
    {
        global $wpdb;
        if (!isset($_GET['term'])) {
            return array('Error' => 'Missing search query');
        }
        $search = $_GET['term'];
        $query = "SELECT ID as id, post_title as title, post_type FROM $wpdb->posts WHERE post_title LIKE %s AND post_type = %s AND post_status = %s ORDER BY post_title ASC";
        $completeQuery = $wpdb->prepare($query, $search . '%', $this->postType, 'publish');
        $allEvents = $wpdb->get_results($completeQuery);
        return $allEvents;
    }

    /**
     * Endpoint to collect events between start/end dates and ordered by chronological order
     * @param $request
     * @return \WP_Error|\WP_REST_Response
     */
    public function getEventsByTimestamp($request)
    {
        //Increase timeout
        set_time_limit(60 * 5);

        global $wpdb;
        $parameters = $request->get_params();

        // Filter by locations at a specified location and inside a circular radius
        $locationIds = '';
        if ($parameters['latlng']) {
            $latlng = explode(',', preg_replace('/\s+/', '', urldecode($parameters['latlng'])));
            if (count($latlng) != 2) {
                return new \WP_Error('error_message', 'Parameter \'latlng\' is in wrong format', array('status' => 400));
            }
            $locations = \HbgEventImporter\Helper\Address::getNearbyLocations($latlng[0], $latlng[1], $parameters['distance']);
            // Return if locations is empty
            if (!$locations) {
                return new \WP_Error('error_message', 'There are no events', array('status' => 204));
            }
            $locationIds = implode(',', array_column($locations, 'post_id'));
        }

        // Filter by locations inside a polygon shaped area
        if (count($parameters['arealatlng']) > 2) {
            $areaLocations = $this->getAreaLocations($parameters['arealatlng']);
            // Return if locations is empty
            if (!$areaLocations) {
                return new \WP_Error('error_message', 'There are no events', array('status' => 204));
            }
            $locationIds = implode(',', array_column($areaLocations, 'ID'));
        }

        // Filter by groups taxonomy
        if (!empty($groups = explode(',', trim($parameters['group-id'], ',')))) {
            $groups = $this->getTaxonomyChildren($groups, 'user_groups');
            $groups = implode(',', $groups);
            $groups = trim($groups, ',');
        }

        // Filter by categories taxonomy
        $taxonomies = trim($parameters['category-id'], ',');

        $db_occasions = $wpdb->prefix . "occasions";
        $query =
            "
            SELECT      $wpdb->posts.ID, $wpdb->posts.post_type, $wpdb->posts.post_status, $db_occasions.timestamp_start, $db_occasions.timestamp_end, $db_occasions
            FROM        $wpdb->posts
            LEFT JOIN   $db_occasions ON ($wpdb->posts.ID = $db_occasions.event)
            LEFT JOIN   $wpdb->postmeta postmeta1 ON $wpdb->posts.ID = postmeta1.post_id ";
        $query .= (!empty($locationIds)) ? "LEFT JOIN $wpdb->postmeta postmeta2 ON $wpdb->posts.ID = postmeta2.post_id " : "";
        $query .= (!empty($groups)) ? "LEFT JOIN $wpdb->term_relationships term1 ON ($wpdb->posts.ID = term1.object_id) " : "";
        $query .= (!empty($taxonomies)) ? "LEFT JOIN $wpdb->term_relationships term2 ON ($wpdb->posts.ID = term2.object_id) " : "";
        $query .= "
                    WHERE $wpdb->posts.post_type = %s
                    AND $wpdb->posts.post_status = %s
                    AND ($db_occasions.timestamp_start BETWEEN %d AND %d OR $db_occasions.timestamp_end BETWEEN %d AND %d)
                    AND postmeta1.meta_key = 'internal_event' AND postmeta1.meta_value = {$parameters['internal']} ";
        $query .= (!empty($locationIds)) ? "AND (postmeta2.meta_key = 'location' AND postmeta2.meta_value IN ($locationIds)) " : "";
        $query .= (!empty($groups)) ? "AND (term1.term_taxonomy_id IN ($groups)) " : "";
        $query .= (!empty($taxonomies)) ? "AND (term2.term_taxonomy_id IN ($taxonomies)) " : "";
        $query .= "GROUP BY $wpdb->posts.ID, $db_occasions.timestamp_start, $db_occasions.timestamp_end ";
        $query .= "ORDER BY $db_occasions.timestamp_start ASC ";
        // Limit response if 'page' parameter is set
        if (!empty($parameters['page'])) {
            $page = $this->sanitizePage($parameters['page']);
            $perPage = !empty($parameters['per_page']) ? $this->sanitizePerPage($parameters['per_page']) : 10;
            $offset = ($page * $perPage) - $perPage;
            $query .= "LIMIT {$offset}, {$perPage} ";
        } elseif (!empty($parameters['post-limit'])) { //Backwards compability fix
            $query .= "LIMIT {$parameters['post-limit']} ";
        }

        $completeQuery = $wpdb->prepare($query, $this->postType, 'publish', $parameters['start'], $parameters['end'], $parameters['start'], $parameters['end']);
        $allEvents = $wpdb->get_results($completeQuery);
        $controller = new \WP_REST_Posts_Controller($this->postType);

        $data = array();
        if (!empty($allEvents)) {
            foreach ($allEvents as $post) {
                // Get event as WP_Post
                $post_object = get_post($post->ID);
                // Add current occasion data to post
                $post_object->timestamp_start = $post->timestamp_start;
                $post_object->timestamp_end = $post->timestamp_end;
                $post_object->timestamp_door = $post->timestamp_door;
                // Save WP_Post with WP API formatting
                $posts = $controller->prepare_item_for_response($post_object, $request);
                $data[] = $controller->prepare_response_for_collection($posts);
            }
        } else {
            return new \WP_Error('error_message', 'No event found at this criteria', array('status' => 404));
        }
        return new \WP_REST_Response($data, 200);
    }

    /**
     * Get locations within a specified area
     * @param array $areaPoints Coordinates for a polygon shaped area
     * @return array|void
     */
    public function getAreaLocations($areaPoints = array())
    {
        // Sanitize area points
        foreach ($areaPoints as $key => &$point) {
            $point = preg_replace('/\s+/', '', urldecode($point));
            // Remove if there's not 2 values
            if (count(explode(',', $point)) != 2) {
                unset($areaPoints[$key]);
            }
        }
        // The first and last polygon coordinates must be identical, to "close" the loop
        if ($areaPoints[0] !== end($areaPoints)) {
            $areaPoints[] = $areaPoints[0];
        }
        // Get array of all locations
        $locations = \HbgEventImporter\Helper\Address::getLocationCoordinates();
        if (is_array($locations) && !empty($locations) && count($areaPoints) > 2) {
            // Filter to return only locations within the polygon area
            $locations = array_filter($locations, function ($location) use ($areaPoints) {
                $pointInPolygon = \HbgEventImporter\Helper\PointInPolygon::pointInPolygon($location['lat'] . ',' . $location['lng'], $areaPoints, true);
                return $pointInPolygon != 'outside' ? true : false;
            });
            return $locations;
        }

        return;
    }

    /**
     * Return term id and its children
     * @param  array  $ids      taxonomy ids
     * @param  string $taxonoym taxonomy name
     * @return array
     */
    public function getTaxonomyChildren($ids, $taxonomy)
    {
        foreach ($ids as $id) {
            if (!empty(get_term_children($id, $taxonomy))) {
                $ids = array_merge($ids, get_term_children($id, $taxonomy));
            }
        }

        return array_unique($ids);
    }

    /**
     * Set current occasion when getting multiple events.
     * @return object
     */
    public function setCurrentOccasion($data, $post, $context)
    {
        $start_date = (!empty($post->timestamp_start)) ? date('Y-m-d H:i', $post->timestamp_start) : null;
        $end_date = (!empty($post->timestamp_end)) ? date('Y-m-d H:i', $post->timestamp_end) : null;
        $door_time = (!empty($post->timestamp_door)) ? date('Y-m-d H:i', $post->timestamp_door) : null;

        if (!empty($data->data['occasions'])) {
            foreach ($data->data['occasions'] as $key => $val) {
                if ($val['start_date'] == $start_date && $val['end_date'] == $end_date && $val['door_time'] == $door_time) {
                    $data->data['occasions'][$key]['current_occasion'] = true;
                }
            }
        }
        return $data;
    }

    /**
     * Get complete list of event occasions
     *
     * @param array           $object     Details of current post.
     * @param string          $field_name Name of field.
     * @param WP_REST_Request $request    Current request
     *
     * @return array
     */
    public function getCompleteOccasions($object, $field_name, $request)
    {
        global $wpdb;
        $db_occasions = $wpdb->prefix . "occasions";
        $id = $object['id'];
        $parameters = $request->get_params();
        $endParam = $parameters['end'] ?? null;
        $data = array();

        // Get upcoming occasions
        $timestamp = strtotime("midnight now") - 1;
        $query = "
        SELECT * FROM {$db_occasions}
        WHERE event = {$id}
        AND timestamp_end > {$timestamp}
        ";
        $query .= ($endParam) ? " AND timestamp_end < {$endParam}" : "";
        $query_results = $wpdb->get_results($query, OBJECT);

        // Get and save occasions from post meta, to get complete data
        $return_value = self::getFieldGetMetaData($object, 'occasions', $request);
        if (is_array($return_value) || is_object($return_value) && !empty($return_value)) {
            foreach ($return_value as $key => $value) {
                // Skip passed occasions and occasions after 'end' parameter
                if (strtotime($value['end_date']) < $timestamp || ($endParam && strtotime($value['start_date']) > $endParam)) {
                    continue;
                }
                $data[] = array(
                    'start_date' => ($value['start_date']) ? $value['start_date'] : null,
                    'end_date' => ($value['end_date']) ? $value['end_date'] : null,
                    'door_time' => ($value['door_time']) ? $value['door_time'] : null,
                    'status' => ($value['status']) ? $value['status'] : null,
                    'occ_exeption_information' => ($value['occ_exeption_information']) ? $value['occ_exeption_information'] : null,
                    'content_mode' => ($value['content_mode']) ? $value['content_mode'] : null,
                    'content' => ($value['content']) ? $value['content'] : null,
                );
            }
        }

        // Save occasions from recurrence rule exceptions that are cancelled or rescheduled
        $recurringRules = self::getFieldGetMetaData($object, 'rcr_rules', $request);
        if (is_array($recurringRules) && !empty($recurringRules)) {
            foreach($recurringRules as $key => $rule) {
                if (isset($rule['rcr_exceptions']) && is_array($rule['rcr_exceptions']) && !empty($rule['rcr_exceptions'])) {
                    foreach ($rule['rcr_exceptions'] as $key => $exception) {
                        $endTimestamp = strtotime($exception['rcr_exc_date'] . ' ' . $rule['rcr_end_time']);
                        if ($exception['status_rcr_exc'] != 'default' && $endTimestamp > $timestamp) {
                            $startTime = !empty($rule['rcr_start_time']) ? new \DateTime($exception['rcr_exc_date'] . ' ' . $rule['rcr_start_time']) : null;
                            $endTime = !empty($rule['rcr_end_time']) ? new \DateTime($exception['rcr_exc_date'] . ' ' . $rule['rcr_end_time']) : null;
                            $doorTime = !empty($rule['rcr_door_time']) ? new \DateTime($exception['rcr_exc_date'] . ' ' . $rule['rcr_door_time']) : null;
                            $data[] = array(
                                'start_date' => $startTime ? date_format($startTime, 'Y-m-d H:i') : null,
                                'end_date' => $endTime ? date_format($endTime, 'Y-m-d H:i') : null,
                                'door_time' => $doorTime ? date_format($doorTime, 'Y-m-d H:i') : null,
                                'status' => $exception['status_rcr_exc'],
                                'occ_exeption_information' => !empty($exception['rcr_exception_info']) ? $exception['rcr_exception_info'] : null,
                                'content_mode' => null,
                                'content' => null,
                            );
                        }
                    }
                }
            }
        }

        // Save remaining occasions from occasions table to array
        foreach ($query_results as $key => $value) {
            $data[] = array(
                'start_date' => ($value->timestamp_start) ? date('Y-m-d H:i', $value->timestamp_start) : null,
                'end_date' => ($value->timestamp_end) ? date('Y-m-d H:i', $value->timestamp_end) : null,
                'door_time' => ($value->timestamp_door) ? date('Y-m-d H:i', $value->timestamp_door) : null,
                'status' => null,
                'occ_exeption_information' => null,
                'content_mode' => null,
                'content' => null,
            );
        }
        $temp = array();
        $keys = array();
        // Remove duplicates from $data array
        foreach ($data as $key => $val) {
            unset($val['status'], $val['occ_exeption_information'], $val['content_mode'], $val['content']);
            if (!in_array($val, $temp)) {
                $temp[] = $val;
                $keys[$key] = true;
            }
        }
        $return_data = array_intersect_key($data, $keys);
        // Sort array by start date
        usort($return_data, function ($x, $y) {
            return strcasecmp($x['start_date'], $y['start_date']);
        });

        if (empty($return_data)) {
            return null;
        }
        return $return_data;
    }

    /**
     * Add data / meta data to organizers field.
     *
     * @param   object $object     The response object.
     * @param   string $field_name The name of the field to add.
     * @param   object $request    The WP_REST_Request object.
     *
     * @return  object|null
     */
    public function organizerData($object, $field_name, $request)
    {
        $return_value = self::getFieldGetMetaData($object, $field_name, $request);

        if (is_array($return_value) || is_object($return_value) && !empty($return_value)) {
            $organizers = $return_value;
        } else {
            return null;
        }

        // 'contacts' field is deprecated, remove in the future
        foreach ($organizers as &$organizer) {
            $organizer = array(
                'main_organizer' => $organizer['main_organizer'],
                'organizer' => get_the_title($organizer['organizer']),
                'organizer_link' => get_field('website', $organizer['organizer']),
                'organizer_phone' => get_field('phone', $organizer['organizer']),
                'organizer_email' => get_field('email', $organizer['organizer']),
                'contact_persons' => get_field('contact_persons', $organizer['organizer']),
                'contacts' => null,
            );
        }

        return $organizers;
    }

    /**
     * Add data / meta data to additional locations field.
     *
     * @param   object $object     The response object.
     * @param   string $field_name The name of the field to add.
     * @param   object $request    The WP_REST_Request object.
     *
     * @return  object|null
     */
    public function additionalLocationData($object, $field_name, $request)
    {
        $return_value = self::getFieldGetMetaData($object, $field_name, $request);

        if (is_array($return_value) || is_object($return_value) && !empty($return_value)) {
            $locations = $return_value;
        } else {
            return null;
        }

        $location_arr = array();
        foreach ($locations as $location) {
            $location = get_post($location);

            if (!$location) {
                continue;
            }
            $parent = !empty($location->post_parent) ? array('id' => $location->post_parent, 'title' => get_the_title($location->post_parent)) : null;
            $location_arr[] = array(
                'id' => $location->ID,
                'title' => $location->post_title,
                'parent' => $parent,
                'content' => $location->post_content,
                'street_address' => get_post_meta($location->ID, 'street_address', true),
                'postal_code' => get_post_meta($location->ID, 'postal_code', true),
                'city' => get_post_meta($location->ID, 'city', true),
                'country' => get_post_meta($location->ID, 'country', true),
                'formatted_address' => get_post_meta($location->ID, 'formatted_address', true),
                'latitude' => get_post_meta($location->ID, 'latitude', true),
                'longitude' => get_post_meta($location->ID, 'longitude', true),
            );
        }

        return $location_arr;
    }

    /**
     * Register rest fields to consumer api
     * @return  void
     * @version 0.3.2 creating consumer accessable meta values.
     */
    public function registerRestFields()
    {
        /* Event tab */

        // Title as plain text
        register_rest_field($this->postType,
            'title',
            array(
                'get_callback' => array($this, 'addPlaintextField'),
                'update_callback' => null,
                'schema' => null,
            )
        );

        // Content as plain text
        register_rest_field($this->postType,
            'content',
            array(
                'get_callback' => array($this, 'addPlaintextField'),
                'update_callback' => null,
                'schema' => null,
            )
        );

        //External event link
        register_rest_field($this->postType,
            'event_link',
            array(
                'get_callback' => array($this, 'stringGetCallBack'),
                'update_callback' => array($this, 'stringUpdateCallBack'),
                'schema' => array(
                    'description' => 'Field containing string value with external link.',
                    'type' => 'string',
                    'context' => array('view', 'edit')
                )
            )
        );

        //Additional event links
        register_rest_field($this->postType,
            'additional_links',
            array(
                'get_callback' => array($this, 'objectGetCallBack'),
                'update_callback' => array($this, 'objectUpdateCallBack'),
                'schema' => array(
                    'description' => 'Field containing array with additional links.',
                    'type' => 'object',
                    'context' => array('view', 'edit')
                )
            )
        );

        //Related events
        register_rest_field($this->postType,
            'related_events',
            array(
                'get_callback' => array($this, 'objectGetCallBack'),
                'update_callback' => array($this, 'objectUpdateCallBack'),
                'schema' => array(
                    'description' => 'Field containing array with related events.',
                    'type' => 'object',
                    'context' => array('view', 'edit')
                )
            )
        );

        /* Occasions tab */

        // Complete list with occasions
        register_rest_field($this->postType,
            'occasions',
            array(
                'get_callback' => array($this, 'getCompleteOccasions'),
                'update_callback' => array($this, 'acfUpdateCallBack'),
                'schema' => array(
                    'description' => 'Field containing array with all event occasions.',
                    'type' => 'object',
                    'context' => array('view', 'edit')
                )
            )
        );

        /* Location tab */

        // Location for the event
        register_rest_field($this->postType,
            'location',
            array(
                'get_callback' => array($this, 'locationData'),
                'update_callback' => array($this, 'stringUpdateCallBack'),
                'schema' => array(
                    'description' => 'Field containing object with location data.',
                    'type' => 'string',
                    'context' => array('view', 'edit', 'embed')
                )
            )
        );

        // Additional locations for the event
        register_rest_field($this->postType,
            'additional_locations',
            array(
                'get_callback' => array($this, 'additionalLocationData'),
                'schema' => array(
                    'description' => 'Field containing object with additional locations data.',
                    'type' => 'object',
                    'context' => array('view')
                )
            )
        );

        /* Organizer tab */

        // Organizers
        register_rest_field($this->postType,
            'organizers',
            array(
                'get_callback' => array($this, 'organizerData'),
                'update_callback' => array($this, 'acfUpdateCallBack'),
                'schema' => array(
                    'description' => 'Field containing object with organizer data.',
                    'type' => 'object',
                    'context' => array('view', 'edit')
                )
            )
        );

        // Sponsors name
        register_rest_field($this->postType,
            'supporters',
            array(
                'get_callback' => array($this, 'objectGetCallBack'),
                'update_callback' => array($this, 'objectUpdateCallBack'),
                'schema' => array(
                    'description' => 'Field containing array with sponsors.',
                    'type' => 'object',
                    'context' => array('view', 'edit')
                )
            )
        );

        /* Booking tab */

        // Link to booking services
        register_rest_field($this->postType,
            'booking_link',
            array(
                'get_callback' => array($this, 'stringGetCallBack'),
                'update_callback' => array($this, 'stringUpdateCallBack'),
                'schema' => array(
                    'description' => 'Field containing string with link to external booking service.',
                    'type' => 'string',
                    'context' => array('view', 'edit')
                )
            )
        );

        // Phone number to booking service
        register_rest_field($this->postType,
            'booking_phone',
            array(
                'get_callback' => array($this, 'stringGetCallBack'),
                'update_callback' => array($this, 'stringUpdateCallBack'),
                'schema' => array(
                    'description' => 'Field containing string with phone number to external booking service.',
                    'type' => 'string',
                    'context' => array('view', 'edit')
                )
            )
        );

        // Phone email to booking service
        register_rest_field($this->postType,
            'booking_email',
            array(
                'get_callback' => array($this, 'stringGetCallBack'),
                'update_callback' => array($this, 'stringUpdateCallBack'),
                'schema' => array(
                    'description' => 'Field containing string with email address to external booking service.',
                    'type' => 'string',
                    'context' => array('view', 'edit')
                )
            )
        );

        // Release date of tickets
        register_rest_field($this->postType,
            'ticket_release_date',
            array(
                'get_callback' => array($this, 'stringGetCallBack'),
                'update_callback' => array($this, 'stringUpdateCallBack'),
                'schema' => array(
                    'description' => 'Field containing date and time when tickets are released',
                    'type' => 'string',
                    'context' => array('view', 'edit')
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

        // Total amount of tickets
        register_rest_field($this->postType,
            'ticket_stock',
            array(
                'get_callback' => array($this, 'stringGetCallBack'),
                'update_callback' => array($this, 'stringUpdateCallBack'),
                'schema' => array(
                    'description' => 'Field containing total amount of tickers',
                    'type' => 'numeric',
                    'context' => array('view', 'edit')
                )
            )
        );

        // Remaining tickets of tickets for sale
        register_rest_field($this->postType,
            'tickets_remaining',
            array(
                'get_callback' => array($this, 'stringGetCallBack'),
                'update_callback' => array($this, 'stringUpdateCallBack'),
                'schema' => array(
                    'description' => 'Field containing remaining amount of tickets for sale.',
                    'type' => 'numeric',
                    'context' => array('view', 'edit')
                )
            )
        );

        //Additional ticket retailers
        register_rest_field($this->postType,
            'additional_ticket_retailers',
            array(
                'get_callback' => array($this, 'objectGetCallBack'),
                'update_callback' => array($this, 'objectUpdateCallBack'),
                'schema' => array(
                    'description' => 'Field containing additional ticket retailers.',
                    'type' => 'object',
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
                    'context' => array('view', 'edit')
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

        // Ticket includes
        register_rest_field($this->postType,
            'ticket_includes',
            array(
                'get_callback' => array($this, 'unformattedStringGetCallBack'),
                'update_callback' => array($this, 'stringUpdateCallBack'),
                'schema' => array(
                    'description' => 'Field containing string with ticket includes information.',
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

        //Additional types of tickets
        register_rest_field($this->postType,
            'additional_ticket_types',
            array(
                'get_callback' => array($this, 'objectGetCallBack'),
                'update_callback' => array($this, 'objectUpdateCallBack'),
                'schema' => array(
                    'description' => 'Field containing object additional types of tickets.',
                    'type' => 'object',
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

        //Price details
        register_rest_field($this->postType,
            'booking_group',
            array(
                'get_callback' => array($this, 'objectGetCallBack'),
                'update_callback' => array($this, 'objectUpdateCallBack'),
                'schema' => array(
                    'description' => 'Field containing object with group prices.',
                    'type' => 'object',
                    'context' => array('view', 'edit')
                )
            )
        );

        //Price range
        register_rest_field($this->postType,
            'price_range',
            array(
                'get_callback' => array($this, 'objectGetCallBack'),
                'update_callback' => array($this, 'objectUpdateCallBack'),
                'schema' => array(
                    'description' => 'Field containing object with information about the price range for tickets.',
                    'type' => 'object',
                    'context' => array('view', 'edit')
                )
            )
        );

        /* Contact info */

        // Contact phone
        register_rest_field($this->postType,
            'contact_phone',
            array(
                'get_callback' => array($this, 'stringGetCallBack'),
                'update_callback' => array($this, 'stringUpdateCallBack'),
                'schema' => array(
                    'description' => 'Field containing string with contact phone number.',
                    'type' => 'string',
                    'context' => array('view', 'edit')
                )
            )
        );

        // Contact email
        register_rest_field($this->postType,
            'contact_email',
            array(
                'get_callback' => array($this, 'stringGetCallBack'),
                'update_callback' => array($this, 'stringUpdateCallBack'),
                'schema' => array(
                    'description' => 'Field containing string with contact email.',
                    'type' => 'string',
                    'context' => array('view', 'edit')
                )
            )
        );

        // Contact information
        register_rest_field($this->postType,
            'contact_information',
            array(
                'get_callback' => array($this, 'stringGetCallBack'),
                'update_callback' => array($this, 'stringUpdateCallBack'),
                'schema' => array(
                    'description' => 'Field containing string with contact information.',
                    'type' => 'string',
                    'context' => array('view', 'edit')
                )
            )
        );

        /* Organizer tab */
        register_rest_field($this->postType,
            'gallery',
            array(
                'get_callback' => array($this, 'objectGetCallBack'),
                'update_callback' => array($this, 'objectUpdateCallBack'),
                'schema' => array(
                    'description' => 'Field containing object with image gallery.',
                    'type' => 'object',
                    'context' => array('view', 'edit')
                )
            )
        );

        /* Social media links */
        register_rest_field($this->postType,
            'facebook',
            array(
                'get_callback' => array($this, 'stringGetCallBack'),
                'update_callback' => array($this, 'stringUpdateCallBack'),
                'schema' => array(
                    'description' => 'Field containing string value with url to social media or streaming services.',
                    'type' => 'string',
                    'context' => array('view', 'edit')
                )
            )
        );

        register_rest_field($this->postType,
            'twitter',
            array(
                'get_callback' => array($this, 'stringGetCallBack'),
                'update_callback' => array($this, 'stringUpdateCallBack'),
                'schema' => array(
                    'description' => 'Field containing string value with url to social media or streaming services.',
                    'type' => 'string',
                    'context' => array('view', 'edit')
                )
            )
        );

        register_rest_field($this->postType,
            'instagram',
            array(
                'get_callback' => array($this, 'stringGetCallBack'),
                'update_callback' => array($this, 'stringUpdateCallBack'),
                'schema' => array(
                    'description' => 'Field containing string value with url to social media or streaming services.',
                    'type' => 'string',
                    'context' => array('view', 'edit')
                )
            )
        );

        register_rest_field($this->postType,
            'google_music',
            array(
                'get_callback' => array($this, 'stringGetCallBack'),
                'update_callback' => array($this, 'stringUpdateCallBack'),
                'schema' => array(
                    'description' => 'Field containing string value with url to social media or streaming services.',
                    'type' => 'string',
                    'context' => array('view', 'edit')
                )
            )
        );

        register_rest_field($this->postType,
            'apple_music',
            array(
                'get_callback' => array($this, 'stringGetCallBack'),
                'update_callback' => array($this, 'stringUpdateCallBack'),
                'schema' => array(
                    'description' => 'Field containing string value with url to social media or streaming services.',
                    'type' => 'string',
                    'context' => array('view', 'edit')
                )
            )
        );

        register_rest_field($this->postType,
            'spotify',
            array(
                'get_callback' => array($this, 'stringGetCallBack'),
                'update_callback' => array($this, 'stringUpdateCallBack'),
                'schema' => array(
                    'description' => 'Field containing string value with url to social media or streaming services.',
                    'type' => 'string',
                    'context' => array('view', 'edit')
                )
            )
        );

        register_rest_field($this->postType,
            'soundcloud',
            array(
                'get_callback' => array($this, 'stringGetCallBack'),
                'update_callback' => array($this, 'stringUpdateCallBack'),
                'schema' => array(
                    'description' => 'Field containing string value with url to social media or streaming services.',
                    'type' => 'string',
                    'context' => array('view', 'edit')
                )
            )
        );

        register_rest_field($this->postType,
            'deezer',
            array(
                'get_callback' => array($this, 'stringGetCallBack'),
                'update_callback' => array($this, 'stringUpdateCallBack'),
                'schema' => array(
                    'description' => 'Field containing string value with url to social media or streaming services.',
                    'type' => 'string',
                    'context' => array('view', 'edit')
                )
            )
        );

        register_rest_field($this->postType,
            'youtube',
            array(
                'get_callback' => array($this, 'objectGetCallBack'),
                'update_callback' => array($this, 'objectUpdateCallBack'),
                'schema' => array(
                    'description' => 'Field containing object with urls to Youtube.',
                    'type' => 'object',
                    'context' => array('view', 'edit')
                )
            )
        );

        register_rest_field($this->postType,
            'vimeo',
            array(
                'get_callback' => array($this, 'objectGetCallBack'),
                'update_callback' => array($this, 'objectUpdateCallBack'),
                'schema' => array(
                    'description' => 'Field containing object with urls to vimeo.',
                    'type' => 'object',
                    'context' => array('view', 'edit')
                )
            )
        );

        /* Add more data to Featured Media field */

        register_rest_field($this->postType,
            'featured_media',
            array(
                'get_callback' => array($this, 'featuredImageData'),
                'schema' => array(
                    'description' => 'Field containing object with featured image data.',
                    'type' => 'string',
                    'context' => array('view', 'edit')
                )
            )
        );

        /* Replace category id with taxonomy name */

        register_rest_field($this->postType,
            'event_categories',
            array(
                'get_callback' => array($this, 'renameTaxonomies'),
                'update_callback' => null,
                'schema' => null
            )
        );

        /* Replace tag id with taxonomy name */

        register_rest_field($this->postType,
            'event_tags',
            array(
                'get_callback' => array($this, 'renameTaxonomies'),
                'update_callback' => null,
                'schema' => null
            )
        );

        /* Replace group id with taxonomy name */

        register_rest_field($this->postType,
            'user_groups',
            array(
                'get_callback' => array($this, 'getTaxonomyCallback'),
                'update_callback' => array($this, 'objectUpdateCallBack'),
                'schema' => null
            )
        );

        /* Import client update callback */

        register_rest_field($this->postType,
            'consumer_client',
            array(
                'update_callback' => array($this, 'stringUpdateCallBack'),
                'schema' => array(
                    'description' => 'Field containing string with consumer name.',
                    'type' => 'string',
                    'context' => array('edit')
                )
            )
        );

        /* Recurring occasions client update callback */

        register_rest_field($this->postType,
            'rcr_rules',
            array(
                'update_callback' => array($this, 'acfUpdateCallBack'),
                'schema' => array(
                    'description' => 'Field containing object with recurring occasion.',
                    'type' => 'object',
                    'context' => array('edit')
                )
            )
        );

        /**
         * Submitter contact details, can be posted from external submit form
         * Hidden from end point
         */
        register_rest_field($this->postType,
            'submitter_email',
            array(
                'update_callback' => array($this, 'stringUpdateCallBack'),
                'schema' => array(
                    'description' => 'Field containing string with submitter email.',
                    'type' => 'string',
                    'context' => array('edit')
                )
            )
        );

        register_rest_field($this->postType,
            'submitter_phone',
            array(
                'update_callback' => array($this, 'stringUpdateCallBack'),
                'schema' => array(
                    'description' => 'Field containing string with submitter phone.',
                    'type' => 'string',
                    'context' => array('edit')
                )
            )
        );
    }
}
