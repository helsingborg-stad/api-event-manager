<?php

namespace HbgEventImporter\Parser;

use \HbgEventImporter\Event as Event;
use \HbgEventImporter\Location as Location;
use \HbgEventImporter\Contact as Contact;
use \HbgEventImporter\Helper\Address as Address;

class Xcap extends \HbgEventImporter\Parser
{
    public function __construct($url)
    {
        parent::__construct($url);
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
        foreach ($events as $key => $event) {
            if (!isset($event->uid) || empty($event->uid)) {
                continue;
            }
            $this->saveEvent($event);
        }
    }

    /**
     * Cleans a single events data into correct format and saves it to db
     * @param  object $eventData  Event data
     * @return void
     */
    public function saveEvent($eventData)
    {
        $address = isset($eventData->{'x-xcap-address'}) && !empty($eventData->{'x-xcap-address'}) ? $eventData->{'x-xcap-address'} : null;
        $alternateName = isset($eventData->uid) && !empty($eventData->uid) ? $eventData->uid : null;
        $categories = isset($eventData->categories) && !empty($eventData->categories) ? array_map('ucwords', array_map('trim', explode(',', $eventData->categories))) : null;
        $description = isset($eventData->description) && !empty($eventData->description) ? $eventData->description : null;
        $doorTime = isset($eventData->dtstart) && !empty($eventData->dtstart) ? $eventData->dtstart : null;
        $doorTime = $this->formatDate($doorTime);
        $endDate = isset($eventData->dtend) && !empty($eventData->dtend) ? $eventData->dtend : null;
        $endDate = $this->formatDate($endDate);
        $location = isset($eventData->location) && !empty($eventData->location) ? $eventData->location : null;
        $name = isset($eventData->summary) && !empty($eventData->summary) ? $eventData->summary : null;
        $startDate = isset($eventData->dtstart) && !empty($eventData->dtstart) ? $eventData->dtstart : null;
        $startDate = $this->formatDate($startDate);
        $ticketUrl = isset($eventData->{'x-xcap-ticketlink'}) && !empty($eventData->{'x-xcap-ticketlink'}) ? $eventData->{'x-xcap-ticketlink'} : null;
        $defaultLocation = get_field('default_city', 'option') ? get_field('default_city', 'option') : null;
        $city = ($location != null) ? $location : $defaultLocation;
        $postStatus = get_field('xcap_post_status', 'option') ? get_field('xcap_post_status', 'option') : 'publish';
        $publish_groups = get_field('xcap_publishing_groups', 'option') ? get_field('xcap_publishing_groups', 'option') : null;

        $image = null;
        if (isset($eventData->{'x-xcap-wideimageid'}) && !empty($eventData->{'x-xcap-wideimageid'}) && $eventData->{'x-xcap-wideimageid'} != 'null') {
            $image = $eventData->{'x-xcap-wideimageid'};
        }
        elseif (isset($eventData->{'x-xcap-imageid'}) && !empty($eventData->{'x-xcap-imageid'}) && $eventData->{'x-xcap-imageid'} != 'null') {
            $image = $eventData->{'x-xcap-imageid'};
        }

        if (!is_string($name)) {
            return;
        }
        $occasions = array();
        if ($startDate != null && $endDate != null && $doorTime != null) {
            $occasions[] = array(
                'start_date' => $startDate,
                'end_date' => $endDate,
                'door_time' => $doorTime
            );
        }

        $postContent = $description;
        $newPostTitle = $name;
        $contactId = null;
        $locationId = null;
        $organizers = array();
        $import_client = 'XCAP';

        // $eventData->{'x-xcap-address'} can return an object instead of a string, then we just want to ignore the location
        if (is_string($address)) {
            // Checking if there is a location already with this title or similar enough
            $locationId = $this->checkIfPostExists('location', $address);
            if ($locationId == null) {
                // Create the location
                $location = new Location(
                    array(
                        'post_title'            =>  $address
                    ),
                    array(
                        'street_address'        =>  null,
                        'postal_code'           =>  null,
                        'city'                  =>  $city,
                        'municipality'          =>  null,
                        'country'               =>  null,
                        'latitude'              =>  null,
                        'longitude'             =>  null,
                        'import_client'         =>  $import_client,
                        '_event_manager_uid'    =>  null,
                        'accepted'              =>  1,
                    )
                );

                $creatSuccess = $location->save();
                if ($creatSuccess) {
                    $locationId = $location->ID;
                    ++$this->nrOfNewLocations;
                    $this->levenshteinTitles['location'][] = array('ID' => $location->ID, 'post_title' => $address);
                }
            }
        }

        $eventId = $this->checkIfPostExists('event', $newPostTitle);
        $isUpdate = false;
        $accepted = -1;
        // Check: if event is a duplicate and if "sync" option is set.
        if ($eventId && get_post_meta( $eventId, '_event_manager_uid', true)) {
            $existingUid = get_post_meta( $eventId, '_event_manager_uid', true);
            $sync = get_post_meta( $eventId, 'sync', true);
            $accepted = get_post_meta($eventId, 'accepted', true);
            $isUpdate = ($existingUid == $eventData->uid && $sync == 1 ) ? true : false;
            $postStatus = get_post_status($eventId);
        }

        // Save event if it doesn't exist or is an update and "sync" option is set. Continues if event is an older version of a duplicate.
        if (($eventId == null || $isUpdate == true) && $this->filter($categories) == true) {
            // Creates the event object
            $event = new Event(
                array(
                    'post_title'              => $newPostTitle,
                    'post_content'            => $postContent,
                    'post_status'             => $postStatus,
                ),
                array(
                    'uniqueId'                => $eventData->uid,
                    '_event_manager_uid'      => $eventData->uid,
                    'sync'                    => true,
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
                    'accepted'                => $accepted,
                    'import_client'           => 'xcap',
                    'imported_event'          => true,
                    'event_publishing_groups' => $publish_groups,
                    'event_unbelonging_group' => $publish_groups == null ? 1 : 0,
                )
            );

            $creatSuccess = $event->save();
            if ($creatSuccess) {
                if ($isUpdate == false) {
                    ++$this->nrOfNewEvents;
                }
                $this->levenshteinTitles['event'][] = array('ID' => $event->ID, 'post_title' => $newPostTitle);
            }

            if (!is_null($event->image)) {
                $event->setFeaturedImageFromUrl($event->image);
            }
        }
    }

    /**
     * Filter, if add or not to add
     * @param  array $categories All categories
     * @return bool
     */
    public function filter($categories)
    {
        $passes = true;
        if (get_field('xcap_filter_categories', 'options')) {
            $filters = array_map('trim', explode(',', get_field('xcap_filter_categories', 'options')));
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

        // Create UTC date object
        $returnDate = new \DateTime(date('Y-m-d H:i', strtotime($dateString)));
        $timeZone = new \DateTimeZone('Europe/Stockholm');
        $returnDate->setTimezone($timeZone);

        return str_replace(' ', 'T', $returnDate->format('Y-m-d H:i:s'));
    }
}
