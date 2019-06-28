<?php

namespace HbgEventImporter\Parser;

use \HbgEventImporter\Event as Event;
use \HbgEventImporter\Location as Location;
use \HbgEventImporter\Organizer as Organizer;

ini_set('memory_limit', '256M');
ini_set('default_socket_timeout', 60*10);

class CbisEvent extends \HbgEventImporter\Parser\Cbis
{
    /**
     * Caches timezone setting to prevent fething multiple times (may occur if wpcache is not working)
     * @var array
     */
    private $timeZoneString;

    /**
     * Holds a list of all found events
     * @var array
     */
    private $events = array();

    /**
     * Start the parsing!
     * @return void
     */
    public function start()
    {
        $this->collectDataForLevenshtein();

        $requestParams = array();

        // CBIS API keys and settings
        $cbisKey        = $this->apiKeys['cbis_key'];
        $cbisId         = $this->apiKeys['cbis_geonode'];
        $cbisCategory   = $this->apiKeys['cbis_event_id'];
        $userGroups     = (is_array($this->apiKeys['cbis_groups']) && ! empty($this->apiKeys['cbis_groups'])) ? array_map('intval', $this->apiKeys['cbis_groups']) : null;

        // Used to set unique key on events
        $shortKey       = substr(intval($this->apiKeys['cbis_key'], 36), 0, 4);

        // Get post status that new event should be created with
        $postStatus     = get_field('cbis_post_status', 'option') ? get_field('cbis_post_status', 'option') : 'publish';

        // Number of products to get, 2000 to get all
        $requestParams['itemsPerPage'] = (int) apply_filters('event/parser/cbis/import/limit', 2000);

        // Get and save "Events"
        $response = $this->soapRequest($cbisKey, $cbisId, $cbisCategory, $requestParams);
        $this->events = $response->ListAllResult->Items->Product;

        if (is_array($this->events) && !empty($this->events)) {
            foreach ($this->events as $key => $eventData) {
                $this->saveEvent($eventData, $postStatus, $userGroups, $shortKey);
            }
        }
    }

    /**
     * Get categories from event data
     * @param  object $eventData Event data object
     * @return array             Categories
     */
    public function getCategories($eventData)
    {
        $categories = array();

        if (is_array($eventData->Categories->Category)) {
            foreach ($eventData->Categories->Category as $category) {
                $categories[] = $category->Name;
            }
        } else {
            $categories[] = $eventData->Categories->Category->Name;
        }

        $categories = array_map('trim', $categories);
        $categories = array_map('ucwords', $categories);

        return $categories;
    }

    /**
     * Get occasions from the event data
     * @param  object $eventData Event data object
     * @return array             Occasions
     */
    public function getOccasions($eventData)
    {
        $occasionsToRegister = array();
        $occasions = $eventData->Occasions;

        if (isset($eventData->Occasions->OccasionObject) && !empty((array)$eventData->Occasions->OccasionObject)) {
            $occasions = $eventData->Occasions->OccasionObject;
        }

        if (!is_array($occasions)) {
            $occasions = array($occasions);
        }

        foreach ($occasions as $occasion) {
            $startDate = null;
            $endDate = null;
            $doorTime = null;

            if (isset($occasion->StartDate)) {
                $startDate = explode('T', $occasion->StartDate)[0] . 'T' . explode('T', $occasion->StartTime)[1];
            }

            if (isset($occasion->EndDate)) {
                $endDate = explode('T', $occasion->EndDate)[0] . 'T' . explode('T', $occasion->EndTime)[1];

                if (strtotime($endDate) <= strtotime($startDate)) {
                    $newEndTime = null;

                    if (isset($occasion->StartDate)) {
                        $date = strtotime($startDate);
                        $newEndTime = date('Y-m-d H:i:s', strtotime("+ 1 hour", $date));
                    }

                    $endDate = str_replace(' ', 'T', $newEndTime);
                }
            }

            if (isset($occasion->EntryTime)) {
                $doorTime = explode('T', $occasion->StartDate)[0] . 'T' . explode('T', $occasion->EntryTime)[1];

                if (explode('T', $occasion->EntryTime)[1]=='00:00:00' && isset($occasion->StartDate)) {
                    $doorTime = $startDate;
                }
            }

            $occasionsToRegister[] = array(
                'start_date' => $startDate,
                'end_date' => $endDate,
                'door_time' => $doorTime
            );
        }

        return $occasionsToRegister;
    }

    /**
     * Cleans a single events data into correct format and saves it to db
     * @param  object   $eventData      Event data
     * @param  string   $postStatus     default post status
     * @param  array    $userGroups     default user groups
     * @param  int      $shortKey       shortened api key
     * @return void
     */
    public function saveEvent($eventData, $postStatus, $userGroups, $shortKey)
    {
        $locationId = $this->maybeCreateLocation($eventData, $postStatus, $userGroups, $shortKey);
        $organizer  = $this->maybeCreateOrganizer($eventData, $postStatus, $userGroups, $shortKey);
        $this->maybeCreateEvent($eventData, $postStatus, $userGroups, $shortKey, $locationId, $organizer);
    }

    /**
     * Creates or updates a location if possible
     * @param  object $eventData  The event data
     * @param  string $postStatus Default post status
     * @param  string $shortKey   The shortkey for uud
     * @return boolean|integer    False if noting done else the ID of the location
     * @throws \Exception
     */
    public function maybeCreateLocation($eventData, $postStatus, $userGroups, $shortKey)
    {
        $importClient = 'CBIS: Event';
        $attributes = $this->getAttributes($eventData);

        if (isset($eventData->Occasions->OccasionObject) && is_array($eventData->Occasions->OccasionObject) && isset($eventData->Occasions->OccasionObject[0]->ArenaName)) {
            $arenaName = $eventData->Occasions->OccasionObject[0]->ArenaName;
        } else {
            $arenaName = null;
        }

        $address = $this->getAttributeValue(self::ATTRIBUTE_ADDRESS, $attributes);
        if (empty($title = $arenaName ? $arenaName : $address)) {
            return false;
        }

        // Check if the location already exist
        $locationId = $this->checkIfPostExists('location', $title);
        $uid = 'cbis-' . $shortKey . '-' . $this->cleanString($title);
        $locPostStatus = $postStatus;
        $isUpdate = false;

        // Check if this is a duplicate or update and if "sync" option is set.
        if ($locationId && get_post_meta($locationId, '_event_manager_uid', true)) {
            $existingUid = get_post_meta($locationId, '_event_manager_uid', true);
            $sync = get_post_meta($locationId, 'sync', true);
            $locPostStatus = get_post_status($locationId);

            if ($existingUid === $uid && $sync == 1) {
                $isUpdate = true;
            }
        }

        // Bail if existing post and not update
        if ($locationId && !$isUpdate) {
            return $locationId;
        }

        // Proceed with updating/creating the location
        $country = $this->getAttributeValue(self::ATTRIBUTE_COUNTRY, $attributes);
        if (is_numeric($country)) {
            $country = "Sweden";
        }

        $latitude = $this->getAttributeValue(self::ATTRIBUTE_LATITUDE, $attributes);
        $longitude = $this->getAttributeValue(self::ATTRIBUTE_LONGITUDE, $attributes);

        if ($latitude == '0') {
            $latitude = null;
        }

        if ($longitude == '0') {
            $longitude = null;
        }

        $city = !empty($eventData->GeoNode->Name) ? $eventData->GeoNode->Name : $this->apiKeys['default_city'];

        // Create the location
        try {
            $location = new Location(
                array(
                    'post_title' => $title,
                    'post_status' => $locPostStatus,
                ),
                array(
                    'street_address' => $address,
                    'postal_code' => $this->getAttributeValue(self::ATTRIBUTE_POSTCODE, $attributes),
                    'city' => $city,
                    'municipality' => $this->getAttributeValue(self::ATTRIBUTE_MUNICIPALITY, $attributes),
                    'country' => $country,
                    'latitude' => $latitude,
                    'longitude' => $longitude,
                    'import_client' => $importClient,
                    '_event_manager_uid' => $uid,
                    'user_groups' => $userGroups,
                    'sync' => 1,
                    'imported_post' => 1,
                )
            );
        } catch (\Exception $e) {
            error_log(print_r($e, true));
            return false;
        }

        if (!$location->save()) {
            return false;
        }

        $this->levenshteinTitles['location'][] = array(
            'ID' => $location->ID,
            'post_title' => $title
        );

        return $location->ID;
    }

    /**
     * Creates or updates an organizer if possible
     * Not used anymore
     * @param  object   $eventData      The event data
     * @param  string   $postStatus     Default post status
     * @param  array    $userGroups     User groups
     * @param  string   $shortKey       UUID short key
     * @return boolean|int              False if fail else organizer ID
     */
    public function maybeCreateOrganizer($eventData, $postStatus, $userGroups, $shortKey)
    {
        $attributes = $this->getAttributes($eventData);

        $title = $this->getAttributeValue(self::ATTRIBUTE_ORGANIZER, $attributes);
        $email = ($this->getAttributeValue(self::ATTRIBUTE_ORGANIZER_EMAIL, $attributes)) ? $this->getAttributeValue(self::ATTRIBUTE_ORGANIZER_EMAIL, $attributes) : $this->getAttributeValue(self::ATTRIBUTE_CONTACT_EMAIL, $attributes);
        $phone = $this->getAttributeValue(self::ATTRIBUTE_PHONE_NUMBER, $attributes);
        $phone = strlen($phone) >= 5 ? $phone : null;

        if (empty($title) && ! empty($email)) {
            $title = $email;
        }

        // Bail if title is empty
        if (empty($title)) {
            return false;
        }

        $contactId = $this->checkIfPostExists('organizer', $title);

        // Get unique string
        $uniqueString = ($email) ? strtolower($email) : str_replace(' ', '', $title);

        $uid = 'cbis-' . $shortKey . '-' . $uniqueString;
        $orgPostStatus = $postStatus;
        $isUpdate = false;

        // Check if this is a duplicate or update and if "sync" option is set.
        if ($contactId && get_post_meta($contactId, '_event_manager_uid', true)) {
            $existingUid = get_post_meta($contactId, '_event_manager_uid', true);
            $sync = get_post_meta($contactId, 'sync', true);
            $orgPostStatus = get_post_status($contactId);

            if ($existingUid == $uid && $sync == 1) {
                $isUpdate = true;
            }
        }

        if ($contactId && !$isUpdate) {
            return $contactId;
        }

        // Save organizer
        $organizer = new Organizer(
            array(
                'post_title'            => $title,
                'post_status'           => $orgPostStatus,
            ),
            array(
                'email'                 => strtolower($this->getAttributeValue(self::ATTRIBUTE_CONTACT_EMAIL, $attributes)),
                'phone'                 => $phone,
                '_event_manager_uid'    => $uid,
                'user_groups'           => $userGroups,
                'sync'                  => 1,
                'imported_post'         => 1,
                'import_client'         => 'cbis',
            )
        );

        if (! $organizer->save()) {
            return false;
        }

        $this->levenshteinTitles['organizer'][] = array(
            'ID' => $organizer->ID,
            'post_title' => $title
        );

        return $organizer->ID;
    }

    /**
     * Creates or updates an event if possible
     * @param  object $eventData  The event data
     * @param  string $postStatus Default post status
     * @param  array $userGroups  User groups
     * @param  string $shortKey   UUID short key
     * @return boolean|int        False if fail else contact id
     */
    public function maybeCreateEvent($eventData, $postStatus, $userGroups, $shortKey, $locationId = null, $organizer = null)
    {
        $attributes = $this->getAttributes($eventData);
        $categories = $this->getCategories($eventData);
        $occasions = $this->getOccasions($eventData);

        $title = $this->getAttributeValue(self::ATTRIBUTE_NAME, $attributes, $eventData->Name);
        $title = str_replace(" (copy)", "", trim($title), $count);

        $postContent = $this->getAttributeValue(self::ATTRIBUTE_DESCRIPTION, $attributes);
        if (!empty($this->getAttributeValue(self::ATTRIBUTE_INGRESS, $attributes))) {
            $postContent = $this->getAttributeValue(self::ATTRIBUTE_INGRESS, $attributes) . "<!--more-->\n\n" . $postContent;
        }

        $newImage = isset($eventData->Image->Url) ? $eventData->Image->Url : null;
        $eventId = $this->checkIfPostExists('event', $title);
        $occurred = false;
        $eventManagerUid = (get_post_meta($eventId, '_event_manager_uid', true)) ? get_post_meta($eventId, '_event_manager_uid', true) : 'cbis-' . $shortKey . '-' . $eventData->Id;

        $organizerArray = (! empty($organizer)) ? array(array(
                            'main_organizer' => true,
                            'organizer'      => intval($organizer)
                        )) : null;

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
                'post_title'              => $title,
                'post_content'            => $postContent,
                'post_status'             => $postStatus
            ),
            array(
                '_event_manager_uid'      => $eventManagerUid,
                'sync'                    => 1,
                'status'                  => isset($eventData->Status) && !empty($eventData->Status) ? $eventData->Status : null,
                'image'                   => $newImage,
                'event_link'              => $this->getAttributeValue(self::ATTRIBUTE_EVENT_LINK, $attributes),
                'categories'              => $categories,
                'occasions'               => $occasions,
                'location'                => !is_null($locationId) ? $locationId : null,
                'organizer'               => $organizerArray,
                'booking_link'            => $this->getAttributeValue(self::ATTRIBUTE_BOOKING_LINK, $attributes),
                'booking_phone'           => $this->getAttributeValue(self::ATTRIBUTE_BOOKING_PHONE_NUMBER, $attributes),
                'age_restriction'         => $this->getAttributeValue(self::ATTRIBUTE_AGE_RESTRICTION, $attributes),
                'price_information'       => $this->getAttributeValue(self::ATTRIBUTE_PRICE_INFORMATION, $attributes),
                'price_adult'             => $this->getAttributeValue(self::ATTRIBUTE_PRICE_ADULT, $attributes),
                'price_children'          => $this->getAttributeValue(self::ATTRIBUTE_PRICE_CHILD, $attributes),
                'import_client'           => 'cbis',
                'imported_post'           => 1,
                'user_groups'             => $userGroups,
                'occurred'                => $occurred,
                'internal_event'          => 0
            )
        );

        if (!$event->save()) {
            return false;
        }

        if (!$eventId) {
            $this->levenshteinTitles['event'][] = array(
                'ID'         => $event->ID,
                'post_title' => $title,
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
     * Filter, if add or not to add
     * @param  array $categories All categories
     * @return bool
     */
    public function filter($categories)
    {
        $passes = true;
        $exclude = $this->apiKeys['cbis_exclude'];

        if (!empty($exclude)) {
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
     * Formats a GMT date to europe stockholm date
     * @param  string $date The GMT date string
     * @return string       The Europe/Stockholm date string
     */
    public function formatDate($date)
    {
        // Format the date string correctly
        $dateParts  = explode("T", $date);
        $timeString = substr($dateParts[1], 0, 5);
        $dateString = $dateParts[0] . ' ' . $timeString;

        // Create UTC date object
        $date = new \DateTime($dateString);

        //Get timezon from wp
        if (!$this->timeZoneString) {
            $this->timeZoneString = get_option('timezone_string');
        }

        //Create new date time for timezone
        $date->setTimezone(
            new \DateTimeZone($this->timeZoneString)
        );

        return $date->format('Y-m-d H:i:s');
    }
}
