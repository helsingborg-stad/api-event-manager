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

    public function start()
    {
        $xml = simplexml_load_file($this->url);
        $xml = json_decode(json_encode($xml));
        $events = $xml->iCal->vevent;

        $index = 0;

        $this->collectDataForLevenshtein();

        $allKeys = array();

        //var_dump($events);
        //die();
        $index = 0;
        foreach ($events as $event) {
            if($index > 50)
                break;
            ++$index;
            /*foreach($event as $key => $value) {
                if(isset($allKeys[$key]))
                    ++$allKeys[$key];
                else
                    $allKeys[$key] = 0;
            }*/

            if (!isset($event->uid) || empty($event->uid)) {
                continue;
            }

            $this->saveEvent($event);
            continue;
            //var_dump($duration);
            die();
            //

            if ($name === null || is_object($name)) {
                continue;
            }

            // Check if the event passes the filter
            if (!$this->filter($categories)) {
                continue;
            }
        }
        die();
    }

    public function saveEvent($eventData)
    {
        global $wpdb;
        $address = isset($eventData->{'x-xcap-address'}) && !empty($eventData->{'x-xcap-address'}) ? $eventData->{'x-xcap-address'} : null;
        $alternateName = isset($eventData->uid) && !empty($eventData->uid) ? $eventData->uid : null;
        $categories = isset($eventData->categories) && !empty($eventData->categories) ? explode(',', $eventData->categories) : null;
        $description = isset($eventData->description) && !empty($eventData->description) ? $eventData->description : null;
        $doorTime = isset($eventData->dtstart) && !empty($eventData->dtstart) ? $eventData->dtstart : null;
        $endDate = isset($eventData->dtend) && !empty($eventData->dtend) ? $eventData->dtend : null;
        $image = isset($eventData->{'x-xcap-imageid'}) && !empty($eventData->{'x-xcap-imageid'}) ? $eventData->{'x-xcap-imageid'} : null;
        $location = isset($eventData->location) && !empty($eventData->location) ? $eventData->location : null;
        $name = isset($eventData->summary) && !empty($eventData->summary) ? $eventData->summary : null;
        $startDate = isset($eventData->dtstart) && !empty($eventData->dtstart) ? $eventData->dtstart : null;
        $ticketUrl = isset($eventData->{'x-xcap-ticketlink'}) && !empty($eventData->{'x-xcap-ticketlink'}) ? $eventData->{'x-xcap-ticketlink'} : null;

        if(!is_string($name))
            return;
        $occasions = array();
        if($startDate != null && $endDate != null && $doorTime != null)
        {
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

        // $eventData->{'x-xcap-address'} can return an object instead of a string, then we just want to ignore the location
        if(is_string($address))
        {
            /*$res = Address::gmapsGetAddressComponents($address . ' ' . $location != null ? $location : '');

            if (!isset($res->geometry->location)) {
                echo "No geometry location:\n";
                return;
            }

            update_post_meta($this->ID, 'map', array(
                'address' => $res->formatted_address,
                'lat' => $res->geometry->location->lat,
                'lng' => $res->geometry->location->lng
            ));

            update_post_meta($this->ID, 'formatted_address', $res->formatted_address);*/

            //var_dump($res);
            //die();
            //
            // Checking if there is a contact already with this title or similar enough
            /*$contactId = $this->checkIfPostExists('contact', $address);

            if ($contactId == null) {
                // Save contact
                $contact = new Contact(
                    array(
                        'post_title'            =>  $address
                    ),
                    array(
                        'name'                  =>  null,
                        'email'                 =>  null,
                        'phone_number'          =>  null,
                        '_event_manager_uid'    =>  null
                    )
                );

                $contactId = $contact->save();

                $this->levenshteinTitles['contact'][] = array('ID' => $contactId, 'post_title' => $address);
            }
            return;*/

            // Checking if there is a location already with this title or similar enough
            $locationId = $this->checkIfPostExists('location', $address);
            if($locationId == null)
            {
                // Create the location
                $location = new Location(
                    array(
                        'post_title'            =>  $address
                    ),
                    array(
                        'street_address'        =>  null,
                        'postal_code'           =>  null,
                        'city'                  =>  $location != null ? $location : null,
                        'municipality'          =>  null,
                        'country'               =>  null,
                        'latitude'              =>  null,
                        'longitude'             =>  null,

                        '_event_manager_uid'    =>  null
                    )
                );

                $locationId = $location->save();

                $this->levenshteinTitles['location'][] = array('ID' => $locationId, 'post_title' => $address);
            }
            else
                echo "Location already exists: " . $locationId . "\n";
        }

        return;

        // Remove when done
        $stuff = array(
                'address'       => $address,
                'alternateName' => $alternateName,
                'categories'    => $categories,
                'description'   => $description,
                'doorTime'      => $this->formatDate($doorTime),
                'duration'      => 0,
                'endDate'       => $this->formatDate($endDate),
                'image'         => $image,
                'location'      => $location,
                'name'          => $name,
                'startDate'     => $this->formatDate($startDate),
                'ticketUrl'     => $ticketUrl
            );

        $eventId = $this->checkIfPostExists('event', $newPostTitle);
        $eventId = null;
        if ($eventId == null) {
            // Creates the event object
            $event = new Event(
                array(
                    'post_title'            => $newPostTitle,
                    'post_content'          => $postContent
                ),
                array(
                    'uniqueId'              => $eventData->uid,
                    '_event_manager_uid'    => $eventData->uid,
                    'sync'                  => true,
                    'status'                => 'Active',
                    'image'                 => isset($image) ? $image : null,
                    'alternate_name'        => $alternateName,
                    'event_link'            => null,
                    'categories'            => $categories,
                    'occasions'             => $occasions,
                    'location'              => (array) $locationId,
                    'organizer'             => '',
                    'organizer_phone'       => null,
                    'organizer_email'       => null,
                    'coorganizer'           => null,
                    'contacts'              => !is_null($contactId) ? (array) $contactId : null,
                    'booking_link'          => $ticketUrl,
                    'booking_phone'         => null,
                    'age_restriction'       => null,
                    'price_information'     => null,
                    'price_adult'           => null,
                    'price_children'        => null,
                    'accepted'              => 0
                )
            );

            //$eventId = $event->save();

            $this->levenshteinTitles['event'][] = array('ID' => $eventId, 'post_title' => $newPostTitle);

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
            $passes = false;

            foreach ($filters as $filter) {
                if (in_array(strtolower($filter), $categoriesLower)) {
                    $passes = true;
                }
            }
        }

        return $passes;
    }

    /**
     * Get how long the event is in seconds
     * @param  string $startDate Date event starts
     * @param  string $endDate   Date event ends
     * @return string  Example return 3600; (1 hour)
     */
    public function getSecondsFromDates($startDate, $endDate)
    {
        $startParts = explode("T", $startDate);
        $endParts = explode("T", $endDate);
        $totalSeconds = (strtotime($endParts[0] . '-' . substr($endParts[1], 0, 4)) - strtotime($startParts[0] . '-' . substr($startParts[1], 0, 4)));
        return $totalSeconds;
    }

    public function formatDate($date)
    {
        // Format the date string corretly
        $dateParts = explode("T", $date);
        $dateString = substr($dateParts[0], 0, 4) . '-' . substr($dateParts[0], 4, 2) . '-' . substr($dateParts[0], 6, 2);
        $timeString = substr($dateParts[1], 0, 4);
        $timeString = substr($timeString, 0, 2) . ':' . substr($timeString, 2, 2);
        $dateString = $dateString . ' ' . $timeString;

        // Create UTC date object
        $date = new \DateTime(date('Y-m-d H:i', strtotime($dateString)));
        $timeZone = new \DateTimeZone('Europe/Stockholm');
        $date->setTimezone($timeZone);

        return str_replace(' ', 'T', $date->format('Y-m-d H:i:s'));
    }
}
