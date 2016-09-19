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
        $query = "SELECT ID, post_title, post_type FROM $wpdb->posts WHERE post_title LIKE %s AND post_type = %s AND post_status = %s ORDER BY post_title ASC";
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
        $occ_door = null;
        if (!is_null($post->timestamp_door)) {
            $occ_door = date('Y-m-d H:i', $post->timestamp_door);
        }
        $data[ $post->ID ] = array(
            'event_id'                  => $id,
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
            'image_src'                 => $image,
            'alternative_name'          => get_field('alternative_name', $id),
            'event_link'                => get_field('event_link', $id),
            'additional_links'          => get_field('additional_links', $id),
            'related_events'            => get_field('related_events', $id),
            'location'                  => get_field('location', $id),
            'additional_locations'      => get_field('additional_locations', $id),
            'organizer'                 => get_field('organizer', $id),
            'organizer_link'            => get_field('organizer_link', $id),
            'organizer_phone'           => get_field('organizer_phone', $id),
            'organizer_email'           => get_field('organizer_email', $id),
            'coorganizer'               => get_field('coorganizer', $id),
            'contacts'                  => get_field('contacts', $id),
            'supporters'                => get_field('supporters', $id),
            'booking_link'              => get_field('booking_link', $id),
            'booking_phone'             => get_field('booking_phone', $id),
            'age_restriction'           => get_field('age_restriction', $id),
            'annual_pass'               => get_field('annual_pass', $id),
            'price_information'         => get_field('price_information', $id),
            'ticket_includes'           => get_field('ticket_includes', $id),
            'price_adult'               => get_field('price_adult', $id),
            'price_children'            => get_field('price_children', $id),
            'price_student'             => get_field('price_student', $id),
            'price_senior'              => get_field('price_senior', $id),
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
            'vimeo'                     => get_field('vimeo', $id)
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
        //Alternative name
        register_rest_field($this->postType,
            'alternative_name',
            array(
                'get_callback' => array($this, 'stringGetCallBack'),
                'update_callback' => array($this, 'stringUpdateCallBack'),
                'schema' => array(
                    'description' => 'Field containing string value with an alternative name.',
                    'type' => 'string',
                    'context' => array('view', 'edit')
                )
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

        /* Organizer tab */

        //Organizer name
        register_rest_field($this->postType,
            'organizer',
            array(
                'get_callback' => array($this, 'stringGetCallBack'),
                'update_callback' => array($this, 'stringUpdateCallBack'),
                'schema' => array(
                    'description' => 'Field containing array with occation data.',
                    'type' => 'string',
                    'context' => array('view', 'edit')
                )
            )
        );

        //Organizer phone
        register_rest_field($this->postType,
            'organizer_phone',
            array(
                'get_callback' => array($this, 'stringGetCallBack'),
                'update_callback' => array($this, 'stringUpdateCallBack'),
                'schema' => array(
                    'description' => 'Field containing array with organizer phone number.',
                    'type' => 'string',
                    'context' => array('view', 'edit')
                )
            )
        );

        //Organizer email
        register_rest_field($this->postType,
            'organizer_email',
            array(
                'get_callback' => array($this, 'stringGetCallBack'),
                'update_callback' => array($this, 'stringUpdateCallBack'),
                'schema' => array(
                    'description' => 'Field containing array with organizer email.',
                    'type' => 'string',
                    'context' => array('view', 'edit')
                )
            )
        );

        //Co-organizer
        register_rest_field($this->postType,
            'coorganizer',
            array(
                'get_callback' => array($this, 'stringGetCallBack'),
                'update_callback' => array($this, 'stringUpdateCallBack'),
                'schema' => array(
                    'description' => 'Field containing array with co-organizer name.',
                    'type' => 'string',
                    'context' => array('view', 'edit')
                )
            )
        );

        //Link to booking services
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

        //Phone number to booking service
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

        //Age restriction details
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
    }
}
