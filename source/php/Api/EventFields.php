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
        add_action('rest_api_init', array($this, 'registerRestRoute'));
        add_action('rest_api_init', array($this, 'registerRestRouteSearch'));
    }

    public static function registerRestRoute()
    {
        $response = register_rest_route('wp/v2', '/event/time', array(
            'methods' => 'GET',
            'callback' => array($this, 'getEventsByTimestamp'),
        ));
    }

    public static function registerRestRouteSearch()
    {
        register_rest_route( 'wp/v2','/event/search', array(
            'methods' => 'GET',
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
    public function getEventsByTimestamp()
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
            return $this->errorMessage('Format not ok', array(0, 1));
        }

        $db_occasions = $wpdb->prefix . "occasions";
        $query =
        "
        SELECT      *
        FROM        $wpdb->posts
        LEFT JOIN   $db_occasions
                    ON $wpdb->posts.ID = $db_occasions.event
        WHERE       $wpdb->posts.post_type = %s
                    AND $wpdb->posts.post_status = %s
                    AND ($db_occasions.timestamp_start BETWEEN %d AND %d OR $db_occasions.timestamp_end BETWEEN %d AND %d)
                    ORDER BY $db_occasions.timestamp_start ASC
        "
        ;

        $timePlusWeek = 0;

        if (isset($_GET['end'])) {
            $time2 = $_GET['end'];
            $timestamp2 = $time2;
            if (!is_numeric($time2)) {
                $timestamp2 = strtotime($timestamp2);
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

        $data = array();
        if (! empty($allEvents)) {
            foreach ($allEvents as $post) {
                $data = $this->makeData($post, $data);
            }
        } else {
            return array('Error' => 'There are no events');
        }

        //$response = new \WP_REST_Response($allEvents);
        return $data;
    }


    /**
     * Add current post to response data for this route.
     * @param \WP_Post $post Current post object.
     * @param array $data Current collection of data
     * @return array
     */
    public function makeData($post, $data)
    {
        $id = $post->event;
        $image = get_post_thumbnail_id($id);
        if ($image) {
            $_image = wp_get_attachment_image_src($image, 'large');
            if (is_array($_image)) {
                $image = $_image[0];
            }
        }
        $occ_start = date('Y-m-d H:i', $post->timestamp_start);
        $occ_end = date('Y-m-d H:i', $post->timestamp_end);
        $occ_door = (!is_null($post->timestamp_door)) ? $occ_door = date('Y-m-d H:i', $post->timestamp_door) : null;

        $data[ $post->ID ] = array(
            'event_id'                  => $id,
            'api_url'                   => rest_url( '/wp/v2/event/' ).$id,
            'post_title'                => $post->post_title,
            'post_author'               => $post->post_author,
            'post_date'                 => $post->post_date,
            'post_date_gmt'             => $post->post_date_gmt,
            'post_content'              => $post->post_content,
            'start_time'                => $occ_start,
            'end_time'                  => $occ_end,
            'door_time'                 => $occ_door,
            'post_status'               => $post->post_status,
            'slug'                      => $post->post_name,
            'post_type'                 => $post->post_type,
            'import_client'             => get_post_meta($id, 'import_client', true),
            'featured_image'            => $image,
            'event_link'                => get_field('event_link', $id),
            'additional_links'          => get_field('additional_links', $id),
            'related_events'            => get_field('related_events', $id),
            'location'                  => get_field('location', $id),
            'additional_locations'      => get_field('additional_locations', $id),
            'organizers'                => get_field('organizers', $id),
            'supporters'                => get_field('supporters', $id),
            'booking_link'              => get_field('booking_link', $id),
            'booking_phone'             => get_field('booking_phone', $id),
            'age_restriction'           => get_field('age_restriction', $id),
            'membership_cards'          => get_field('membership_cards', $id),
            'price_information'         => get_field('price_information', $id),
            'ticket_includes'           => get_field('ticket_includes', $id),
            'price_adult'               => get_field('price_adult', $id),
            'price_children'            => get_field('price_children', $id),
            'children_age'              => get_field('children_age', $id),
            'price_student'             => get_field('price_student', $id),
            'price_senior'              => get_field('price_senior', $id),
            'senior_age'                => get_field('senior_age', $id),
            'booking_group'             => get_field('booking_group', $id),
            'gallery'                   => get_field('gallery', $id),
            'facebook'                  => get_field('facebook', $id),
            'twitter'                   => get_field('twitter', $id),
            'instagram'                 => get_field('instagram', $id),
            'google_music'              => get_field('google_music', $id),
            'apple_music'               => get_field('apple_music', $id),
            'spotify'                   => get_field('spotify', $id),
            'soundcloud'                => get_field('soundcloud', $id),
            'deezer'                    => get_field('deezer', $id),
            'youtube'                   => get_field('youtube', $id),
            'vimeo'                     => get_field('vimeo', $id),
        );
        return $data;
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

        //Event occations
        register_rest_field($this->postType,
            'occasions',
            array(
                'get_callback' => array($this, 'objectGetCallBack'),
                'update_callback' => array($this, 'objectUpdateCallBack'),
                'schema' => array(
                    'description' => 'Field containing array with occasions data.',
                    'type' => 'object',
                    'context' => array('view', 'edit')
                )
            )
        );

        // Recurring event rules
        register_rest_field($this->postType,
            'rcr_rules',
            array(
                'get_callback' => array($this, 'objectGetCallBack'),
                'update_callback' => array($this, 'objectUpdateCallBack'),
                'schema' => array(
                    'description' => 'Field containing array with recurring event rules.',
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
                'get_callback' => array($this, 'objectGetCallBack'),
                'update_callback' => array($this, 'objectUpdateCallBack'),
                'schema' => array(
                    'description' => 'Field containing object with location.',
                    'type' => 'object',
                    'context' => array('view', 'edit')
                )
            )
        );

        // Additional locations for the event
        register_rest_field($this->postType,
            'additional_locations',
            array(
                'get_callback' => array($this, 'objectGetCallBack'),
                'update_callback' => array($this, 'objectUpdateCallBack'),
                'schema' => array(
                    'description' => 'Field containing array with additional location data.',
                    'type' => 'object',
                    'context' => array('view', 'edit')
                )
            )
        );

        /* Organizer tab */

        // Organizers
        register_rest_field($this->postType,
            'organizers',
            array(
                'get_callback' => array($this, 'objectGetCallBack'),
                'update_callback' => array($this, 'objectUpdateCallBack'),
                'schema' => array(
                    'description' => 'Field containing array with organizers.',
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


    }
}
