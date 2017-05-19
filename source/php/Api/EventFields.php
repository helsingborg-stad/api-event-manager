<?php

namespace HbgEventImporter\Api;

/**
 * Adding meta fields to event post type
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
    }

    public static function registerRestRoute()
    {
        $response = register_rest_route('wp/v2', '/'.$this->postType.'/'.'time', array(
            'methods'  => \WP_REST_Server::READABLE,
            'callback' => array($this, 'getEventsByTimestamp'),
        ));
    }

    public static function registerRestRouteSearch()
    {
        register_rest_route('wp/v2', '/event/search', array(
            'methods'  => \WP_REST_Server::READABLE,
            'callback' => array($this, 'getEventsSearch'),
        ));
    }

    public function getEventsSearch($data)
    {
        global $wpdb;
        if (!isset($_GET['term'])) {
            return array('Error' => 'Missing search query');
        }
        $search = $_GET['term'];
        $query = "SELECT ID as id, post_title as title, post_type FROM $wpdb->posts WHERE post_title LIKE %s AND post_type = %s AND post_status = %s ORDER BY post_title ASC";
        $completeQuery = $wpdb->prepare($query, $search.'%', $this->postType, 'publish');
        $allEvents = $wpdb->get_results($completeQuery);
        return $allEvents;
    }

    public function errorMessage($message)
    {
        $returnArray = array(
            'error'     => $message,
            'Example 1' => rest_url('/wp/v2/event/time?start=' . date('Y-m-d')),
            'Example 2' => rest_url('/wp/v2/event/time?start=' . strtotime("now")),
            'Example 3' => rest_url('/wp/v2/event/time?start=' . date('Y-m-d') . '&end=' . date('Y-m-d', strtotime("now +7 days"))),
            'Example 4' => rest_url('/wp/v2/event/time?start=' . strtotime("now") . '&end=' . strtotime("now +7 days"))
        );

        $returnArray['Format examples'] = 'http://php.net/manual/en/datetime.formats.php';

        return $returnArray;
    }

    /**
     * http://v2.wp-api.org/
     * @return [type] [description]
     */
    public function getEventsByTimestamp($request)
    {
        global $wpdb;
        $week = 604800;
        if (!isset($_GET['start'])) {
            return $this->errorMessage('No variable supplied');
        }

        $time1 = $_GET['start'];
        $timestamp = $time1;

        $post_status = 'publish';

        if (!is_numeric($time1)) {
            $timestamp = strtotime($timestamp);
        }

        if ($timestamp == false) {
            return $this->errorMessage('Format not valid');
        }

        // Get nearby locations from coordinates
        $idString = '';
        if (isset($_GET['latlng'])) {
            $distance   = (isset($_GET['distance'])) ? str_replace(',', '.', $_GET['distance']) : 0;
            $latlng     = explode(',', preg_replace('/\s+/', '', urldecode($_GET['latlng'])));
            if (count($latlng) != 2) {
                return new \WP_Error('error_message', 'Parameter \'latlng\' is in wrong format', array( 'status' => 404 ));
            }
            $locations  = \HbgEventImporter\Helper\Address::getNearbyLocations($latlng[0], $latlng[1], floatval($distance));
            // Return if locations is empty
            if (! $locations) {
                return new \WP_Error('error_message', 'There are no events', array( 'status' => 404 ));
            }
            $idString   = implode(',', array_column($locations, 'post_id'));
        }

        $limit       = (isset($_GET['post-limit']) && is_numeric($_GET['post-limit'])) ? $_GET['post-limit'] : null;
        $groupId     = (isset($_GET['group-id']) && !empty($_GET['group-id'])) ? explode(',', trim($_GET['group-id'], ',')) : null;
        if (! empty($groupId)) {
            $groupId = $this->getTaxonomyChildren($groupId, 'user_groups');
            $groupId = implode(',', $groupId);
        }
        $categoryId  = (isset($_GET['category-id'])) ? trim($_GET['category-id'], ',') : '';
        $taxonomies  = ($groupId) ? $groupId . ',' . $categoryId : $categoryId;
        $taxonomies  = trim($taxonomies, ',');

        $db_occasions = $wpdb->prefix . "occasions";
        $query =
        "
        SELECT      *
        FROM        $wpdb->posts
        LEFT JOIN   $db_occasions ON ($wpdb->posts.ID = $db_occasions.event)
        LEFT JOIN   $wpdb->postmeta postmeta ON $wpdb->posts.ID = postmeta.post_id
        LEFT JOIN   $wpdb->term_relationships ON ($wpdb->posts.ID = $wpdb->term_relationships.object_id)
        WHERE       $wpdb->posts.post_type = %s
                    AND $wpdb->posts.post_status = %s
                    AND ($db_occasions.timestamp_start BETWEEN %d AND %d OR $db_occasions.timestamp_end BETWEEN %d AND %d)
        ";
        $query .= (! empty($taxonomies)) ? "AND ($wpdb->term_relationships.term_taxonomy_id IN ($taxonomies)) " : "";
        $query .= (! empty($groupId)) ? "AND (postmeta.meta_key = 'missing_user_group' AND postmeta.meta_value = 0) " : "";
        $query .= (! empty($idString)) ? "AND (postmeta.meta_key = 'location' AND postmeta.meta_value IN ($idString)) " : "";
        $query .= "GROUP BY $db_occasions.timestamp_start, $db_occasions.timestamp_end ";
        $query .= "ORDER BY $db_occasions.timestamp_start ASC";
        $query .= ($limit != null) ? " LIMIT " . $limit : "";

        $timePlusWeek = 0;

        if (isset($_GET['end'])) {
            $time2 = $_GET['end'];
            $timestamp2 = $time2;
            if (!is_numeric($time2)) {
                // Add 1 day to include events occuring on end date
                $timestamp2 = strtotime($timestamp2 . " +1 days");
            }
            if ($timestamp2 == false) {
                return $this->errorMessage('Format not ok', array(2, 3));
            }
            $timePlusWeek = $timestamp2;
        } else {
            $timePlusWeek = $timestamp + $week;
        }

        $completeQuery = $wpdb->prepare($query, $this->postType, $post_status, $timestamp, $timePlusWeek, $timestamp, $timePlusWeek);
        $allEvents = $wpdb->get_results($completeQuery);
        $controller = new \WP_REST_Posts_Controller($this->postType);

        $data = array();
        if (! empty($allEvents)) {
            foreach ($allEvents as $post) {
                // Get event as WP_Post
                $post_object = get_post($post->event);
                // Add current occasion data to post
                $post_object->timestamp_start = $post->timestamp_start;
                $post_object->timestamp_end   = $post->timestamp_end;
                $post_object->timestamp_door  = $post->timestamp_door;
                // Save WP_Post with WP API formatting
                $posts = $controller->prepare_item_for_response($post_object, $request);
                $data[] = $controller->prepare_response_for_collection($posts);
            }
        } else {
            return new \WP_Error('error_message', 'There are no events', array( 'status' => 404 ));
        }
        return new \WP_REST_Response($data, 200);
    }

    /**
     * Return term id and its children
     * @param  array  $ids       taxnomy ids
     * @param  string $taxonoym  taxonomy name
     * @return array
     */
    public function getTaxonomyChildren($ids, $taxonomy)
    {
        foreach ($ids as $id) {
            if (! empty(get_term_children($id, $taxonomy))) {
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
        $start_date = (! empty($post->timestamp_start)) ? date('Y-m-d H:i', $post->timestamp_start) : null;
        $end_date = (! empty($post->timestamp_end)) ? date('Y-m-d H:i', $post->timestamp_end) : null;
        $door_time = (! empty($post->timestamp_door)) ? date('Y-m-d H:i', $post->timestamp_door) : null;

        if (! empty($data->data['occasions'])) {
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
     * @param array $object Details of current post.
     * @param string $field_name Name of field.
     * @param WP_REST_Request $request Current request
     *
     * @return array
     */
    public function getCompleteOccasions($object, $field_name, $request)
    {
        global $wpdb;
        $db_occasions = $wpdb->prefix . "occasions";
        $id = $object['id'];
        $data = array();

        // Get upcoming occasions
        $timestamp = strtotime("midnight now") - 1;
        $query =
        "
        SELECT * FROM $db_occasions
        WHERE event = $id
        AND timestamp_end > $timestamp
        ";
        $query_results = $wpdb->get_results($query, OBJECT);

        // Get and save occasions from post meta, to get complete data
        $return_value = self::getFieldGetMetaData($object, 'occasions', $request);
        if (is_array($return_value)||is_object($return_value) && !empty($return_value)) {
            foreach ($return_value as $key => $value) {
                // Skip passed occasions
                if (strtotime($value['end_date']) < $timestamp) {
                    continue;
                }
                $data[] = array(
                'start_date'               => ($value['start_date']) ? $value['start_date'] : null,
                'end_date'                 => ($value['end_date']) ? $value['end_date'] : null,
                'door_time'                => ($value['door_time']) ? $value['door_time'] : null,
                'status'                   => ($value['status']) ? $value['status'] : null,
                'occ_exeption_information' => ($value['occ_exeption_information']) ? $value['occ_exeption_information'] : null,
                'content_mode'             => ($value['content_mode']) ? $value['content_mode'] : null,
                'content'                  => ($value['content']) ? $value['content'] : null,
                );
            }
        }
        // Save remaining occasions from occasions table to array
        foreach ($query_results as $key => $value) {
            $data[] = array(
                'start_date'               => ($value->timestamp_start) ? date('Y-m-d H:i', $value->timestamp_start) : null,
                'end_date'                 => ($value->timestamp_end) ? date('Y-m-d H:i', $value->timestamp_end) : null,
                'door_time'                => ($value->timestamp_door) ? date('Y-m-d H:i', $value->timestamp_door) : null,
                'status'                   => null,
                'occ_exeption_information' => null,
                'content_mode'             => null,
                'content'                  => null,
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
     * @param   object  $object      The response object.
     * @param   string  $field_name  The name of the field to add.
     * @param   object  $request     The WP_REST_Request object.
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

        $organizers_arr = array();
        foreach ($organizers as $organizer) {
            $contacts_array = array();
            if (! empty($organizer['contacts']) && is_array($organizer['contacts'])) {
                foreach ($organizer['contacts'] as $contact) {
                    $contacts_array[] = array(
                    'title' => get_the_title($contact->ID),
                    'name' => get_field('name', $contact->ID),
                    'phone_number' => get_field('phone_number', $contact->ID),
                    'email' => get_field('email', $contact->ID),
                    );
                }
                $organizer['contacts'] = $contacts_array;
            }
            $organizers_arr[] = $organizer;
        }
        return $organizers_arr;
    }

    /**
     * Add data / meta data to additional locations field.
     *
     * @param   object  $object      The response object.
     * @param   string  $field_name  The name of the field to add.
     * @param   object  $request     The WP_REST_Request object.
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

            if (! $location) {
                continue;
            }
            $parent = ! empty($location->post_parent) ? array('id' => $location->post_parent, 'title' => get_the_title($location->post_parent)) : null;
            $location_arr[] = array(
                'id'                => $location->ID,
                'title'             => $location->post_title,
                'parent'            => $parent,
                'content'           => $location->post_content,
                'street_address'    => get_post_meta($location->ID, 'street_address', true),
                'postal_code'       => get_post_meta($location->ID, 'postal_code', true),
                'city'              => get_post_meta($location->ID, 'city', true),
                'country'           => get_post_meta($location->ID, 'country', true),
                'formatted_address' => get_post_meta($location->ID, 'formatted_address', true),
                'latitude'          => get_post_meta($location->ID, 'latitude', true),
                'longitude'         => get_post_meta($location->ID, 'longitude', true),
            );
        }

        return $location_arr;
    }

    /**
     * Adds additional data to featured_media field.
     *
     * @param   object  $object      The response object.
     * @param   string  $field_name  The name of the field to add.
     * @param   object  $request     The WP_REST_Request object.
     *
     * @return  object|null
     */
    public function featuredImageData($object, $field_name, $request)
    {
        // Proceed if the post has a featured image.
        if (! empty($object['featured_media'])) {
            $image_id = (int)$object['featured_media'];
        } else {
            return null;
        }

        $image = get_post($image_id);

        if (! $image) {
            return null;
        }

        $featured_image['id']            = $image_id;
        $featured_image['alt_text']      = get_post_meta($image_id, '_wp_attachment_image_alt', true);
        $featured_image['caption']       = $image->post_excerpt;
        $featured_image['description']   = $image->post_content;
        $featured_image['media_type']    = wp_attachment_is_image($image_id) ? 'image' : 'file';
        $featured_image['post']          = ! empty($image->post_parent) ? (int) $image->post_parent : null;
        $featured_image['source_url']    = wp_get_attachment_url($image_id);

        return apply_filters('featured_image_data', $featured_image, $image_id);
    }

    /**
     * Register rest fields to consumer api
     * @return  void
     * @version 0.3.2 creating consumer accessable meta values.
     */
    public static function registerRestFields()
    {
        /* Event tab */

        // Title as plain text
        register_rest_field($this->postType,
            'title',
            array(
                'get_callback'    => array($this, 'addPlaintextField'),
                'update_callback' => null,
                'schema'          => null,
            )
        );

        // Content as plain text
        register_rest_field($this->postType,
            'content',
            array(
                'get_callback'    => array($this, 'addPlaintextField'),
                'update_callback' => null,
                'schema'          => null,
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
                'get_callback'    => array($this, 'getCompleteOccasions'),
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
                    'type' => 'object',
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
                    'type' => 'object',
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
    }
}
