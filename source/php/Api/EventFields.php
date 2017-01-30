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
        add_action('rest_api_init', array($this, 'registerRestRoute'));
        add_action('rest_api_init', array($this, 'registerRestRouteSearch'));
        add_action('rest_api_init', array($this, 'registerRestFields'));
        add_filter('rest_prepare_event', array($this, 'setCurrentOccasion'), 10, 3 );
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

    public function errorMessage($message, array $texts)
    {
        $examples = array(rest_url('/wp/v2/event/time?start=1462060800'),
            rest_url('/wp/v2/event/time?start=2016-05-01'),
            rest_url('/wp/v2/event/time?start=1462060800&end=1470009600'),
            rest_url('/wp/v2/event/time?start=2016-05-01&end=2016-08-01'));
        $returnArray = array('error' => $message);
        foreach ($texts as $text) {
            $returnArray['Example ' . ($text+1)] = $examples[$text];
        }

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
            return $this->errorMessage('No variable supplied', array(0, 1));
        }

        $time1 = $_GET['start'];
        $timestamp = $time1;

        $post_status = 'publish';

        if (!is_numeric($time1)) {
            $timestamp = strtotime($timestamp);
        }

        if ($timestamp == false) {
            return $this->errorMessage('Format not valid', array(0, 1));
        }

        $limit = (isset($_GET['post-limit']) && is_numeric($_GET['post-limit'])) ? $_GET['post-limit'] : null;

        $groupId = (isset($_GET['group-id'])) ? trim($_GET['group-id'], ',') : '';
        $categoryId = (isset($_GET['category-id'])) ? trim($_GET['category-id'], ',') : '';
        $taxonomies  = $groupId;
        $taxonomies .= ($groupId) ? ',' . $categoryId : $categoryId;
        $taxonomies = trim($taxonomies, ',');

        $db_occasions = $wpdb->prefix . "occasions";
        $query =
        "
        SELECT      *
        FROM        $wpdb->posts
        LEFT JOIN   $db_occasions ON ($wpdb->posts.ID = $db_occasions.event)
        LEFT JOIN   $wpdb->term_relationships ON ($wpdb->posts.ID = $wpdb->term_relationships.object_id)
        WHERE       $wpdb->posts.post_type = %s
                    AND $wpdb->posts.post_status = %s
                    AND ($db_occasions.timestamp_start BETWEEN %d AND %d OR $db_occasions.timestamp_end BETWEEN %d AND %d)
        ";
        $query .= (! empty($taxonomies)) ? "AND ($wpdb->term_relationships.term_taxonomy_id IN ($taxonomies))" : "";

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
            return new \WP_Error('Error', 'There are no events', array( 'status' => 404 ));
        }
        return new \WP_REST_Response($data, 200);
    }

    /**
     * Register rest fields to consumer api
     * @return  void
     * @version 0.3.2 creating consumer accessable meta values.
     */
    public static function registerRestFields()
    {
        /* Event tab */

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
                'schema' => array(
                    'description' => 'Field containing array with all event occasions.',
                    'type' => 'object',
                    'context' => array('view')
                )
            )
        );

        /* Location tab */

        // Location for the event
        register_rest_field($this->postType,
            'location',
            array(
                'get_callback' => array($this, 'locationData'),
                'schema' => array(
                    'description' => 'Field containing object with location data.',
                    'type' => 'object',
                    'context' => array('view')
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
                'schema' => array(
                    'description' => 'Field containing object with organizer data.',
                    'type' => 'object',
                    'context' => array('view')
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
                'get_callback' => array($this, 'stringGetCallBack'),
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
                'get_callback' => array($this, 'stringGetCallBack'),
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
                    'context' => array('view')
                )
            )
        );

        /* Replace category id with taxonomy name */

        register_rest_field($this->postType,
            'event_categories',
            array(
                'get_callback' => array($this, 'renameTaxonomies'),
                'schema' => array(
                    'description' => 'Field containing object with taxonomies.',
                    'type' => 'object',
                    'context' => array('view')
                )
            )
        );

        /* Replace tag id with taxonomy name */

        register_rest_field($this->postType,
            'event_tags',
            array(
                'get_callback' => array($this, 'renameTaxonomies'),
                'schema' => array(
                    'description' => 'Field containing object with taxonomies.',
                    'type' => 'object',
                    'context' => array('view')
                )
            )
        );

        /* Replace group id with taxonomy name */

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

    }
}
