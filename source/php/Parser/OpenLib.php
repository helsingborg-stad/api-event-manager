<?php

namespace HbgEventImporter\Parser;

use \HbgEventImporter\Event as Event;
use \HbgEventImporter\Location as Location;
use \HbgEventImporter\Helper\Address as Address;

ini_set('memory_limit', '256M');
ini_set('default_socket_timeout', 60 * 10);

class OpenLib extends \HbgEventImporter\Parser
{
    public function __construct($url, $apiKeys)
    {
        parent::__construct($url, $apiKeys);
    }

    /**
     * Get Event data from TransTicket
     * @return array data
     */
    private function getEventData()
    {
        error_log($this->apiKeys['api_key']);
        error_log($this->apiKeys['group_id']);

        $url = $this->url;
        $url .= '?apikey=' . $this->apiKeys['api_key'];
        /* TODO:
        replace with this line
        $url .= '&since=' . date('Y-m-d', strtotime("-1 year"));
        */
        $url .= '&since=2018-12-21';
        $url .= '&pageSize=10';
        $url .= '&libraryGroupId=' . $this->apiKeys['group_id'];

        error_log($url);

        $eventData = \HbgEventImporter\Helper\Curl::request(
            'GET',
            $url,
            false,
            false,
            'json',
            array('Content-Type: application/json')
        );

        return json_decode($eventData);
    }

    /**
     * Start the parsing!
     * @return void
     */
    public function start()
    {
        $eventData = $this->getEventData();
        /*
        TODO:
        Loop over requests with page param
        */
        $this->collectDataForLevenshtein();

        // Set unique key on events
        $shortKey = substr(md5($this->url), 0, 8);

        foreach ($eventData as $key => $event) {
            if (!isset($event->id) || empty($event->id)) {
                continue;
            }

            $this->saveEvent($event, $shortKey);
        }
    }

    /**
     * Cleans a single events data into correct format and saves it to db
     * @param  object $eventData Event data
     * @param  int    $shortKey  unique key, created from the api url
     * @throws \Exception
     * @return void
     */
    public function saveEvent($eventData, $shortKey)
    {
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

        error_log($eventData->id);
        error_log($data['postTitle']);
        error_log($data['postStatus']);
        error_log($data['postContent']);
        error_log(print_r($data['userGroups'], true));
        error_log(print_r($data['categories'], true));

        //$locationId = $this->maybeCreateLocation($data, $shortKey);
        $locationId = null;
        $this->maybeCreateEvent($data, $shortKey, $locationId);
    }

    /**
     * Creates or updates an event if possible
     * @param  array  $data       Event data
     * @param  string $shortKey   Event unique short key
     * @param  int    $locationId Location id
     * @return boolean|int          Event id or false
     * @throws \Exception
     */
    public function maybeCreateEvent($data, $shortKey, $locationId)
    {
        $eventId = $this->checkIfPostExists('event', $data['postTitle']);
        $occurred = false;

        $eventManagerUid = (get_post_meta($eventId, '_event_manager_uid', true)) ? get_post_meta(
            $eventId,
            '_event_manager_uid',
            true
        ) : 'open-library-' . $shortKey . '-' . $data['uId'];
        $postStatus = $data['postStatus'];
        // Get existing event meta data
        $sync = true;
        if ($eventId) {
            $sync = get_post_meta($eventId, 'sync', true);
            $postStatus = get_post_status($eventId);
            $levenshteinKey = array_search($eventId, array_column($this->levenshteinTitles['event'], 'ID'));
            $occurred = $this->levenshteinTitles['event'][$levenshteinKey]['occurred'];
        }

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
                    'occasions' => array(),
                    'location' => $locationId !== null ? $locationId : null,
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
     * @param array $data Event data
     *
     * @return boolean|int  Location id or false
     *
     * @throws \Exception
     */
    public function maybeCreateLocation($data, $shortKey)
    {
        if (empty($data['address']) && empty($data['name'])) {
            return false;
        }

        // Checking if there is a location already with this title or similar enough
        $locationId = $this->checkIfPostExists('location', $data['name']);

        $locPostStatus = $data['postStatus'];
        $isUpdate = false;
        $postTitle = $data['name'] ?? $data['address'] ?? '';
        $uid = 'open-library-' . $shortKey . '-' . $this->cleanString($postTitle);

        // Check if this is a duplicate or update and if "sync" option is set.
        if ($locationId && get_post_meta($locationId, '_event_manager_uid', true)) {
            $existingUid = get_post_meta($locationId, '_event_manager_uid', true);
            $sync = get_post_meta($locationId, 'sync', true);
            $locPostStatus = get_post_status($locationId);

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
                    'post_status' => $locPostStatus,
                ),
                array(
                    'street_address' => $data['address'] ?? null,
                    'postal_code' => null,
                    'city' => $data['city'],
                    'municipality' => null,
                    'country' => null,
                    'latitude' => $data['latitude'] ?? null,
                    'longitude' => $data['longitude'] ?? null,
                    'import_client' => 'open-library',
                    '_event_manager_uid' => $uid,
                    'user_groups' => $data['userGroups'],
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
