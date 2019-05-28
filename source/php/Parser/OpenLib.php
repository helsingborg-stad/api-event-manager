<?php

namespace HbgEventImporter\Parser;

use \HbgEventImporter\Event as Event;
use \HbgEventImporter\Location as Location;
use \HbgEventImporter\Helper\Address as Address;

ini_set('memory_limit', '256M');
ini_set('default_socket_timeout', 60 * 10);

class OpenLib extends \HbgEventImporter\Parser
{
    private $shortKey;

    public function __construct($url, $apiKeys)
    {
        parent::__construct($url, $apiKeys);
        // Set unique key on events
        $this->shortKey = substr(md5($url), 0, 8);
    }

    /**
     * Start parser
     * @return void
     */
    public function start()
    {
        $page = 0;
        $loop = true;

        $this->collectDataForLevenshtein();

        // Loop over API requests until result is empty
        while ($loop === true) {
            // Build url with params
            $url = add_query_arg(
                    array(
                        'apikey' => $this->apiKeys['api_key'],
                        'since' => date('Y-m-d', strtotime("-1 month")),
                        'pageSize' => 20,
                        'pageIndex' => $page,
                        'libraryGroupId' => $this->apiKeys['group_id'],
                    ),
                $this->url
            );

            // Use Curl to fetch events
            $eventData = \HbgEventImporter\Helper\Curl::request(
                'GET',
                $url,
                false,
                false,
                'json',
                array('Content-Type: application/json')
            );
            // Convert to JSON
            $eventData = json_decode($eventData);

            // Return if result is empty
            if (empty($eventData)) {
                $loop = false;
                break;
            }

            // Save each event
            foreach ($eventData as $event) {
                if (!isset($event->id) || empty($event->id)) {
                    continue;
                }

                $this->saveEvent($event);
            }

            $page++;
        }
    }

    /**
     * Cleans a single events data into correct format and saves it to db
     * @param  object $eventData Event data
     * @throws \Exception
     * @return void
     */
    public function saveEvent($eventData)
    {
        // Deletes event from db
        if ($eventData->changeType === 'Deleted') {
            global $wpdb;
            $eventManagerUid = $this->getEventUid($eventData->id);
            // Check if the post exist in db
            $result = $wpdb->get_row("select post_id from $wpdb->postmeta where meta_value = '{$eventManagerUid}'", ARRAY_N);
            // Delete the event if it exists
            if (isset($result[0]) && is_numeric($result[0])) {
                wp_trash_post((int)$result[0]);
            }

            return;
        }

        $data['uId'] = $eventData->id;
        $data['postTitle'] = !empty($eventData->title) ? strip_tags($eventData->title) : null;
        $data['postContent'] = !empty($eventData->contentZones) ? implode("\n", (array)$eventData->contentZones) : '';
        $data['postContent'] .= !empty($eventData->otherInformation) ? $eventData->otherInformation : '';
        $data['postContent'] = strip_tags($data['postContent']);
        $data['image'] = !empty($eventData->imageUrl) ? $eventData->imageUrl : null;
        $data['event_link'] = !empty($eventData->url) ? $eventData->url : null;
        $data['postStatus'] = get_field('ols_post_status', 'option') ? get_field('ols_post_status', 'option') : 'publish';
        $data['userGroups'] = (is_array($this->apiKeys['default_groups']) && !empty($this->apiKeys['default_groups'])) ?
            array_map(
                'intval',
                $this->apiKeys['default_groups']
            ) : null;
        $data['categories'] = isset($eventData->tags) && is_array($eventData->tags) ? $this->sanitizeCategories($eventData->tags) : array();

        $data['occasions'] = array();
        if (!empty($eventData->occurrences)) {
            foreach ($eventData->occurrences as $occasion) {

                $locationId = !empty($occasion->library) ? $this->maybeCreateLocation($occasion->library, $data['userGroups'], $data['postStatus']) : null;

                $start = new \DateTime($occasion->start);
                $start = $start->format('Y-m-d H:i:s');

                $end = new \DateTime($occasion->stop);
                $end = $end->format('Y-m-d H:i:s');

                $data['occasions'][] = array(
                'start_date' => $start,
                'end_date' => $end,
                'door_time' => $start,
                'status' => $occasion->canceled ? 'cancelled' : 'scheduled',
                'location_mode' => !empty($locationId) ? 'custom' : 'master',
                'location' => !empty($locationId) ? $locationId : null,
                );
            }
        }

        $this->maybeCreateEvent($data);
    }

    /**
     * Creates or updates an event if possible
     * @param  array  $data       Event data
     * @return boolean|int          Event id or false
     * @throws \Exception
     */
    public function maybeCreateEvent($data)
    {
        $eventId = $this->checkIfPostExists('event', $data['postTitle']);
        $occurred = false;

        $eventManagerUid = (get_post_meta($eventId, '_event_manager_uid', true)) ? get_post_meta(
            $eventId,
            '_event_manager_uid',
            true
        ) : $this->getEventUid($data['uId']);
        $postStatus = $data['postStatus'];
        // Get existing event meta data
        $sync = true;
        if ($eventId) {
            $sync = get_post_meta($eventId, 'sync', true);
            $postStatus = get_post_status($eventId);
            $levenshteinKey = array_search($eventId, array_column($this->levenshteinTitles['event'], 'ID'));
            $occurred = $this->levenshteinTitles['event'][$levenshteinKey]['occurred'];
        }

        // Bail if api sync id disabled
        if ($eventId && !$sync) {
            return $eventId;
        }

        try {
            $event = new Event(
                array(
                    'post_title' => $data['postTitle'],
                    'post_content' => $data['postContent'],
                    'post_status' => $postStatus,
                ),
                array(
                    '_event_manager_uid' => $eventManagerUid,
                    'sync' => 1,
                    'status' => 'Active',
                    'image' => $data['image'],
                    'event_link' => $data['event_link'],
                    'categories' => $data['categories'],
                    'occasions' => $data['occasions'],
                    'location' => null,
                    'organizer' => null,
                    'booking_link' => null,
                    'booking_phone' => null,
                    'age_restriction' => null,
                    'price_information' => null,
                    'price_adult' => null,
                    'price_children' => null,
                    'import_client' => 'open-library',
                    'imported_post' => 1,
                    'user_groups' => $data['userGroups'],
                    'occurred' => $occurred,
                    'ticket_stock' => null,
                    'ticket_release_date' => null,
                    'tickets_remaining' => null,
                    'additional_ticket_retailers' => null,
                    'price_range_seated_minimum_price' => null,
                    'price_range_seated_maximum_price' => null,
                    'price_range_standing_minimum_price' => null,
                    'price_range_standing_maximum_price' => null,
                    'additional_ticket_types' => null,
                    'internal_event' => 0
                )
            );
        } catch (\Exception $e) {
            error_log($e);
            return false;
        }

        if (!$event->save()) {
            return false;
        }

        if (!$eventId) {
            $this->levenshteinTitles['event'][] = array(
                'ID' => $event->ID,
                'post_title' => $data['postTitle'],
                'occurred' => true,
            );
        } else {
            $this->levenshteinTitles['event'][$levenshteinKey]['occurred'] = true;
        }

        if (!is_null($event->image)) {
            $event->setFeaturedImageFromUrl($event->image);
        }

        return $event->ID;
    }

    /**
     * Creates or updates a location if possible
     *
     * @param [object] $data Data object
     * @param [array] $userGroups User groups
     * @param [string] $locationPostStatus Post status
     * @return boolean|int  Location id or false
     * @throws \Exception
     */
    public function maybeCreateLocation($data, $userGroups, $locationPostStatus)
    {
        // Bail if essential data is missing
        if (empty($data->name) && empty($data->streetAddress)) {
            return false;
        }

        $postTitle = empty($data->name) ? $data->name : $data->streetAddress;
        // Checking if there is a location already with this title or similar enough
        $locationId = $this->checkIfPostExists('location', $postTitle);
        $isUpdate = false;
        $uid = $this->getEventUid($data->id);

        // Check if this is a duplicate or update and if "sync" option is set.
        if ($locationId && get_post_meta($locationId, '_event_manager_uid', true)) {
            $existingUid = get_post_meta($locationId, '_event_manager_uid', true);
            $sync = get_post_meta($locationId, 'sync', true);
            $locationPostStatus = get_post_status($locationId);

            if ($existingUid == $uid && $sync == 1) {
                $isUpdate = true;
            }
        }

        if ($locationId && !$isUpdate) {
            return $locationId;
        }

        // Create the location
        try {
            $location = new Location(
                array(
                    'post_title' => $postTitle,
                    'post_status' => $locationPostStatus,
                ),
                array(
                    'street_address' => $data->streetAddress ?? null,
                    'postal_code' => $data->zipCode ?? null,
                    'city' => $data->city ?? null,
                    'municipality' => null,
                    'country' => null,
                    'latitude' => $data->latitude ?? null,
                    'longitude' =>  $data->longitude ?? null,
                    'import_client' => 'open-library',
                    '_event_manager_uid' => $uid,
                    'user_groups' => $userGroups,
                    'sync' => 1,
                    'imported_post' => 1,
                )
            );
        } catch (\Exception $e) {
            $location = false;
            error_log($e);
        }

        if (!$location->save()) {
            if ($locationId) {
                return $locationId;
            } else {
                return false;
            }
        }

        $this->levenshteinTitles['location'][] = array('ID' => $location->ID, 'post_title' => $postTitle);

        return $location->ID;
    }

     /**
     * Returns event manager UID
     *
     * @param [mixed] $identifier
     * @return void
     */
    public function getEventUid($identifier)
    {
        return 'open-library-' . $this->shortKey . '-' . $identifier;
    }

    /**
     * Clean categories names
     * @param  object $eventData Event data object
     * @return array             Categories
     */
    public function sanitizeCategories($categories)
    {
        $categories = array_map('trim', $categories);
        $categories = array_map('ucwords', $categories);

        return $categories;
    }
}
