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
    }

    public static function registerRestRoute()
    {
        $response = register_rest_route('/wp/v2/event', '/time/', array(
            'methods' => 'GET',
            'callback' => array($this, 'getEventsByTimestamp'),
        ));
    }

    public function errorMessage($message, array $texts)
    {
        $examples = array(rest_url('/wp/v2/event/time?start=1462060800'),
            rest_url('/wp/v2/event/time?start=2016-05-01'),
            rest_url('/wp/v2/event/time?start=1462060800&end=1470009600'),
            rest_url('/wp/v2/event/time?start=2016-05-01&end=2016-08-01'));
        $returnArray = array('error' => $message);
        foreach($texts as $text) {
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
        if(!isset($_GET['start']))
            return $this->errorMessage('No variable supplied', array(0,1));

        $time1 = $_GET['start'];
        $timestamp = $time1;

        if(!is_numeric($time1))
            $timestamp = strtotime($timestamp);

        if($timestamp == false)
            return $this->errorMessage('Format not ok', array(0,1));

        $query = "SELECT event FROM event_occasions WHERE timestamp_start BETWEEN %d AND %d OR timestamp_end BETWEEN %d AND %d";
        $timePlusWeek = 0;

        if(isset($_GET['end']))
        {
            $time2 = $_GET['end'];
            $timestamp2 = $time2;
            if(!is_numeric($time2))
                $timestamp2 = strtotime($timestamp2);

            if($timestamp2 == false)
                return $this->errorMessage('Format not ok', array(2,3));

            $timePlusWeek = $timestamp2;
        }
        else
            $timePlusWeek = $timestamp + $week;

        $completeQuery = $wpdb->prepare($query, $timestamp, $timePlusWeek, $timestamp, $timePlusWeek);

        $result = $wpdb->get_results($completeQuery);

        if(empty($result))
            return array('Error' => 'There are no events');
        $allEventIds = array();

        foreach($result as $key => $value) {
            $allEventIds[] = $value->event;
        }

        $allEventIds = array_unique($allEventIds);
        $allEvents = $wpdb->get_results("SELECT * FROM event_posts WHERE post_type = '" . $this->postType . "' AND post_status = 'publish' AND ID IN(" . implode(',', $allEventIds) . ")");
        //$response = new \WP_REST_Response($allEvents);
        return $allEvents;
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
                    'description' => 'Field contianing string value with an alternative name.',
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
