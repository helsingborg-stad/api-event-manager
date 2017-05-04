<?php

namespace HbgEventImporter\Parser;

use \HbgEventImporter\Event as Event;
use \HbgEventImporter\Location as Location;
use \HbgEventImporter\Contact as Contact;
use \HbgEventImporter\Helper\Address as Address;

class Xcap extends \HbgEventImporter\Parser
{

    private $timeZoneString;

    public function __construct($url, $apiKeys)
    {
        parent::__construct($url, $apiKeys);
    }

    /**
     * Start the parsing!
     * @return void
     */
    public function start()
    {
        $xml = simplexml_load_file($this->url);
        $xml = json_decode(json_encode($xml));
        $events = $xml->iCal->vevent;

        $this->collectDataForLevenshtein();

        // Used to set unique key on events
        $shortKey = substr(intval($this->url, 36), 0, 4);

        foreach ($events as $key => $event) {
            if (!isset($event->uid) || empty($event->uid)) {
                continue;
            }

            $this->saveEvent($event, $shortKey);
        }
    }

    /**
     * Cleans a single events data into correct format and saves it to db
     * @param  object $eventData  Event data
     * @param  int    $shortKey   unique key, created from the api url
     * @return void
     */
    public function saveEvent($eventData, $shortKey)
    {
        $data['address'] = isset($eventData->{'x-xcap-address'}) && !empty($eventData->{'x-xcap-address'}) ? $eventData->{'x-xcap-address'} : null;
        $data['alternateName'] = isset($eventData->uid) && !empty($eventData->uid) ? $eventData->uid : null;
        $data['categories'] = isset($eventData->categories) && !empty($eventData->categories) ? array_map('ucwords', array_map('trim', explode(',', $eventData->categories))) : null;
        $data['description'] = isset($eventData->description) && !empty($eventData->description) ? $eventData->description : null;
        $data['doorTime'] = isset($eventData->dtstart) && !empty($eventData->dtstart) ? $eventData->dtstart : null;
        $data['doorTime'] = $this->formatDate($data['doorTime']);
        $data['endDate'] = isset($eventData->dtend) && !empty($eventData->dtend) ? $eventData->dtend : null;
        $data['endDate'] = $this->formatDate($data['endDate']);
        $data['location'] = isset($eventData->location) && !empty($eventData->location) ? $eventData->location : null;
        $data['name'] = isset($eventData->summary) && !empty($eventData->summary) ? $eventData->summary : null;
        $data['startDate'] = isset($eventData->dtstart) && !empty($eventData->dtstart) ? $eventData->dtstart : null;
        $data['startDate'] = $this->formatDate($data['startDate']);
        $data['ticketUrl'] = isset($eventData->{'x-xcap-ticketlink'}) && !empty($eventData->{'x-xcap-ticketlink'}) ? $eventData->{'x-xcap-ticketlink'} : null;
        $data['defaultLocation'] = get_field('default_city', 'option') ? get_field('default_city', 'option') : null;
        $data['city'] = ($data['location'] != null) ? $data['location'] : $data['defaultLocation'];
        $data['postStatus'] = get_field('xcap_post_status', 'option') ? get_field('xcap_post_status', 'option') : 'publish';
        $data['user_groups'] = (is_array($this->apiKeys['xcap_groups']) && ! empty($this->apiKeys['xcap_groups'])) ? array_map('intval', $this->apiKeys['xcap_groups']) : null;

        $data['image'] = null;
        if (isset($eventData->{'x-xcap-wideimageid'}) && !empty($eventData->{'x-xcap-wideimageid'}) && $eventData->{'x-xcap-wideimageid'} != 'null') {
            $data['image'] = $eventData->{'x-xcap-wideimageid'};
        } elseif (isset($eventData->{'x-xcap-imageid'}) && !empty($eventData->{'x-xcap-imageid'}) && $eventData->{'x-xcap-imageid'} != 'null') {
            $data['image'] = $eventData->{'x-xcap-imageid'};
        }

        //Add http to strings not containing protocol. The server may correct this to https by redirect.
        if (strpos($data['image'], '//') === 0) {
            $data['image'] = 'http:' . $data['image'];
        }

        if (!is_string($data['name'])) {
            return;
        }

        $data['occasions'] = array();
        if ($data['startDate'] != null && $data['endDate'] != null && $data['doorTime'] != null) {
            $data['occasions'][] = array(
                'start_date' => $data['startDate'],
                'end_date' => $data['endDate'],
                'door_time' => $data['doorTime']
            );
        }

        $locationId = $this->maybeCreateLocation($data, $shortKey);
        $eventId = $this->maybeCreateEvent($eventData, $data, $shortKey, $locationId);
    }

    /**
     * Creates or updates an event if possible
     * @param  array  $data         Event data
     * @param  string $shortKey     Event unique short key
     * @param  int    $locationId   Location id
     * @return boolean|int          Event id or false
     */
    public function maybeCreateEvent($eventData, $data, $shortKey, $locationId)
    {
        extract($data);

        $postContent = $description;
        $postTitle = $name;
        $organizers = array();

        $eventId = $this->checkIfPostExists('event', $postTitle);
        $occurred = false;

        $eventManagerUid = (get_post_meta($eventId, '_event_manager_uid', true)) ? get_post_meta($eventId, '_event_manager_uid', true) : $shortKey . '-' . $eventData->uid;

        // Get existing event meta data
        if ($eventId) {
            $sync           = get_post_meta($eventId, 'sync', true);
            $postStatus     = get_post_status($eventId);
            $levenshteinKey = array_search($eventId, array_column($this->levenshteinTitles['event'], 'ID'));
            $occurred       = $this->levenshteinTitles['event'][$levenshteinKey]['occurred'];
        }

        if (($eventId && !$sync) || !$this->filter($categories)) {
            return $eventId;
        }

        $event = new Event(
            array(
                'post_title'              => $postTitle,
                'post_content'            => $postContent,
                'post_status'             => $postStatus,
            ),
            array(
                '_event_manager_uid'      => $eventManagerUid,
                'sync'                    => 1,
                'status'                  => 'Active',
                'image'                   => !empty($image) ? $image : null,
                'alternate_name'          => $alternateName,
                'event_link'              => null,
                'categories'              => $categories,
                'occasions'               => $occasions,
                'location'                => $locationId != null ? $locationId : null,
                'organizers'              => $organizers,
                'booking_link'            => is_string($ticketUrl) ? $ticketUrl : null,
                'booking_phone'           => null,
                'age_restriction'         => null,
                'price_information'       => null,
                'price_adult'             => null,
                'price_children'          => null,
                'import_client'           => 'xcap',
                'imported_post'           => 1,
                'user_groups'             => $user_groups,
                'missing_user_group'      => $user_groups == null ? 1 : 0,
                'occurred'                => $occurred,
            )
        );

        if (!$event->save()) {
            return false;
        }

        if (!$eventId) {
            $this->nrOfNewEvents++;
            $this->levenshteinTitles['event'][] = array(
                'ID'         => $event->ID,
                'post_title' => $postTitle,
                'occurred'   => true,
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
     * @param  array $data  Event data
     * @return boolean|int  Location id or false
     */
    public function maybeCreateLocation($data, $shortKey)
    {
        extract($data);

        if (!is_string($address)) {
            return false;
        }

        // Checking if there is a location already with this title or similar enough
        $locationId = $this->checkIfPostExists('location', $address);

        $locPostStatus = $postStatus;
        $isUpdate = false;

        // Check if this is a duplicate or update and if "sync" option is set.
        if ($locationId && get_post_meta($locationId, '_event_manager_uid', true)) {
            $existingUid = get_post_meta($locationId, '_event_manager_uid', true);
            $sync = get_post_meta($locationId, 'sync', true);
            $locPostStatus = get_post_status($locationId);

            if ($existingUid == 'xcap-' . $shortKey . '-' . $this->cleanString($address) && $sync == 1) {
                $isUpdate = true;
            }
        }

        if ($locationId && !$isUpdate) {
            return $locationId;
        }

        // Create the location
        $location = new Location(
            array(
                'post_title'            => $address,
                'post_status'           => $locPostStatus,
            ),
            array(
                'street_address'        => null,
                'postal_code'           => null,
                'city'                  => $city,
                'municipality'          => null,
                'country'               => null,
                'latitude'              => null,
                'longitude'             => null,
                'import_client'         => 'xcap',
                '_event_manager_uid'    => 'xcap-' . $shortKey . '-' . $this->cleanString($address),
                'user_groups'           => $user_groups,
                'missing_user_group'    => $user_groups == null ? 1 : 0,
                'sync'                  => 1,
                'imported_post'         => 1,
            )
        );

        if (!$location->save()) {
            return false;
        }

        if ($isUpdate == false) {
            $this->nrOfNewLocations++;
        }

        $this->levenshteinTitles['location'][] = array('ID' => $location->ID, 'post_title' => $address);

        return $location->ID;
    }

    /**
     * Filter, if add or not to add
     * @param  array $categories All categories
     * @return bool
     */
    public function filter($categories)
    {
        $passes = true;
        $exclude = $this->apiKeys['xcap_exclude'];

        if (! empty($exclude)) {
            $filters = array_map('trim', explode(',', $exclude));
            $categoriesLower = array_map('strtolower', $categories);

            foreach ($filters as $filter) {
                if (in_array(strtolower($filter), $categoriesLower)) {
                    $passes = false;
                }
            }
        }

        return $passes;
    }

    /**
     * Edit the date format we get from xcap api
     * @param  string $date example 20160105T141429Z
     * @return string example
     */
    public function formatDate($date)
    {
        if ($date == null) {
            return $date;
        }
        // Format the date string corretly
        $dateParts = explode("T", $date);
        $dateString = substr($dateParts[0], 0, 4) . '-' . substr($dateParts[0], 4, 2) . '-' . substr($dateParts[0], 6, 2);
        $timeString = substr($dateParts[1], 0, 4);
        $timeString = substr($timeString, 0, 2) . ':' . substr($timeString, 2, 2);
        $dateString = $dateString . ' ' . $timeString;

        //Get timezon from wp
        if (!$this->timeZoneString) {
            $this->timeZoneString = get_option('timezone_string');
        }

        // Create UTC date object
        $returnDate = new \DateTime(date('Y-m-d H:i', strtotime($dateString)));
        $timeZone = new \DateTimeZone($this->timeZoneString);
        $returnDate->setTimezone($timeZone);

        return str_replace(' ', 'T', $returnDate->format('Y-m-d H:i:s'));
    }
}
