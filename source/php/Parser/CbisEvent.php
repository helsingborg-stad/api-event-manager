<?php

namespace HbgEventImporter\Parser;

use \HbgEventImporter\Event as Event;
use \HbgEventImporter\Location as Location;
use \HbgEventImporter\Contact as Contact;

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
        global $wpdb;

        $this->collectDataForLevenshtein();

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
        $getLength = (int) apply_filters('event/parser/cbis/import/limit', 2000);

        // Get and save "Events"
        $response = $this->soapRequest($cbisKey, $cbisId, $cbisCategory, $getLength);
        $this->events = $response->ListAllResult->Items->Product;

        // Filter expired products older than 2 years
        $filteredProducts = array_filter($this->events, function ($obj) {
            if (isset($obj->ExpirationDate) && strtotime($obj->ExpirationDate) < strtotime("-2 years")) {
                return false;
            }

            return true;
        });

        foreach ($filteredProducts as $key => $eventData) {
            $this->saveEvent($eventData, $postStatus, $userGroups, $shortKey);
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

        if (isset($eventData->Occasions->OccasionObject) && count($eventData->Occasions->OccasionObject) > 0) {
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
     * @param  array    $userGroups  default user groups
     * @param  int      $shortKey       shortened api key
     * @return void
     */
    public function saveEvent($eventData, $postStatus, $userGroups, $shortKey)
    {
        $attributes = $this->getAttributes($eventData);
        $categories = $this->getCategories($eventData);
        $occasions = $this->getOccasions($eventData);

        $newPostTitle = $this->getAttributeValue(self::ATTRIBUTE_ADDRESS, $attributes) ? $this->getAttributeValue(self::ATTRIBUTE_ADDRESS, $attributes) : $eventData->GeoNode->Name;

        $locationId = $this->checkIfPostExists('location', $newPostTitle);

        $uid = 'cbis-' . $shortKey . '-' . $this->cleanString($newPostTitle);
        $locPostStatus = $postStatus;
        $isUpdate = false;

        // Check if this is a duplicate or update and if "sync" option is set.
        if ($locationId && get_post_meta($locationId, '_event_manager_uid', true)) {
            $existingUid   = get_post_meta($locationId, '_event_manager_uid', true);
            $sync          = get_post_meta($locationId, 'sync', true);
            $locPostStatus = get_post_status($locationId);
            $isUpdate      = ($existingUid == $uid && $sync == 1) ? true : false;
        }

        if ($locationId == null || $isUpdate == true) {
            $country = $this->getAttributeValue(self::ATTRIBUTE_COUNTRY, $attributes);

            if (is_numeric($country)) {
                $country = "Sweden";
            }

            $import_client = 'CBIS: Event';

            // Create the location
            $latitude = $this->getAttributeValue(self::ATTRIBUTE_LATITUDE, $attributes) != '0' ? $this->getAttributeValue(self::ATTRIBUTE_LATITUDE, $attributes) : null;
            $longitude = $this->getAttributeValue(self::ATTRIBUTE_LONGITUDE, $attributes) != '0' ? $this->getAttributeValue(self::ATTRIBUTE_LONGITUDE, $attributes) : null;

            $location = new Location(
                array(
                    'post_title'            => $newPostTitle,
                    'post_status'           => $locPostStatus,
                ),
                array(
                    'street_address'        => $this->getAttributeValue(self::ATTRIBUTE_ADDRESS, $attributes),
                    'postal_code'           => $this->getAttributeValue(self::ATTRIBUTE_POSTCODE, $attributes),
                    'city'                  => $eventData->GeoNode->Name,
                    'municipality'          => $this->getAttributeValue(self::ATTRIBUTE_MUNICIPALITY, $attributes),
                    'country'               => $country,
                    'latitude'              => $latitude,
                    'longitude'             => $longitude,
                    'import_client'         => $import_client,
                    '_event_manager_uid'    => $uid,
                    'user_groups'           => $userGroups,
                    'missing_user_group'    => $userGroups == null ? 1 : 0,
                    'sync'                  => 1,
                    'imported_post'         => 1,
                )
            );

            $creatSuccess = $location->save();

            if ($creatSuccess) {
                $locationId = $location->ID;

                if ($isUpdate == false) {
                    ++$this->nrOfNewLocations;
                }

                $this->levenshteinTitles['location'][] = array('ID' => $location->ID, 'post_title' => $newPostTitle);
            }
        }

        $newPostTitle = $this->getAttributeValue(self::ATTRIBUTE_CONTACT_PERSON, $attributes) != null ? $this->getAttributeValue(self::ATTRIBUTE_CONTACT_PERSON, $attributes) : '';

        if ($this->getAttributeValue(self::ATTRIBUTE_CONTACT_EMAIL, $attributes) != null) {
            if (!empty($newPostTitle)) {
                $newPostTitle .= ' : ';
            }

            $newPostTitle .= $this->getAttributeValue(self::ATTRIBUTE_CONTACT_EMAIL, $attributes);
        }

        $contactId = null;

        if (!empty($newPostTitle)) {
            $contactId = $this->checkIfPostExists('contact', $newPostTitle);

            $uniqueString = ($this->getAttributeValue(self::ATTRIBUTE_CONTACT_EMAIL, $attributes) != null) ? strtolower($this->getAttributeValue(self::ATTRIBUTE_CONTACT_EMAIL, $attributes)) : strtolower(preg_replace(' ', '', $newPostTitle));

            $uid = 'cbis-' . $shortKey . '-' . $uniqueString;
            $conPostStatus = $postStatus;
            $isUpdate = false;

            // Check if this is a duplicate or update and if "sync" option is set.
            if ($contactId && get_post_meta($contactId, '_event_manager_uid', true)) {
                $existingUid   = get_post_meta($contactId, '_event_manager_uid', true);
                $sync          = get_post_meta($contactId, 'sync', true);
                $conPostStatus = get_post_status($contactId);
                $isUpdate      = ($existingUid == $uid && $sync == 1) ? true : false;
            }

            if ($contactId == null || $isUpdate == true) {
                $phoneNumber = $this->getAttributeValue(self::ATTRIBUTE_PHONE_NUMBER, $attributes);
                // Save contact
                $contact = new Contact(
                    array(
                        'post_title'            => $newPostTitle,
                        'post_status'           => $conPostStatus,
                    ),
                    array(
                        'name'                  => $this->getAttributeValue(self::ATTRIBUTE_CONTACT_PERSON, $attributes),
                        'email'                 => strtolower($this->getAttributeValue(self::ATTRIBUTE_CONTACT_EMAIL, $attributes)),
                        'phone_number'          => $phoneNumber == null ? $phoneNumber : (strlen($phoneNumber) > 5 ? $phoneNumber : null),
                        '_event_manager_uid'    => $uid,
                        'user_groups'           => $userGroups,
                        'missing_user_group'    => $userGroups == null ? 1 : 0,
                        'sync'                  => 1,
                        'imported_post'         => 1,
                        'import_client'         => 'cbis',
                    )
                );

                $creatSuccess = $contact->save();
                $contactId = $contact->ID;
                if ($creatSuccess) {
                    if ($isUpdate == false) {
                        ++$this->nrOfNewContacts;
                    }
                    $this->levenshteinTitles['contact'][] = array('ID' => $contact->ID, 'post_title' => $newPostTitle);
                }
            }
        }

        $organizers = array();
        if (!empty($this->getAttributeValue(self::ATTRIBUTE_PHONE_NUMBER, $attributes)) || !empty($this->getAttributeValue(self::ATTRIBUTE_ORGANIZER_EMAIL, $attributes)) || !is_null($contactId)) {
            $organizers[] = array(
                'organizer'       => '',
                'organizer_link'  => '',
                'organizer_phone' => $this->getAttributeValue(self::ATTRIBUTE_PHONE_NUMBER, $attributes),
                'organizer_email' => $this->getAttributeValue(self::ATTRIBUTE_ORGANIZER_EMAIL, $attributes),
                'contacts'        => !is_null($contactId) ? (array) $contactId : null,
                'main_organizer'  => true
            );

            if (!empty($this->getAttributeValue(self::ATTRIBUTE_CO_ORGANIZER, $attributes))) {
                $organizers[] = array(
                'organizer'       => $this->getAttributeValue(self::ATTRIBUTE_CO_ORGANIZER, $attributes),
                'organizer_link'  => '',
                'organizer_phone' => '',
                'organizer_email' => '',
                'contacts'        => '',
                'main_organizer'  => false
                );
            }
        }

        $postContent = $this->getAttributeValue(self::ATTRIBUTE_DESCRIPTION, $attributes);
        if ($this->getAttributeValue(self::ATTRIBUTE_INGRESS, $attributes) && !empty($this->getAttributeValue(self::ATTRIBUTE_INGRESS, $attributes))) {
            $postContent = $this->getAttributeValue(self::ATTRIBUTE_INGRESS, $attributes) . "<!--more-->\n\n" . $postContent;
        }

        $postTitle = $this->getAttributeValue(self::ATTRIBUTE_NAME, $attributes, ($eventData->Name != null ? $eventData->Name : null));
        $newPostTitle = str_replace(" (copy)", "", trim($postTitle), $count);
        $newImage = (isset($eventData->Image->Url) ? $eventData->Image->Url : null);
        $eventId = $this->checkIfPostExists('event', $newPostTitle);
        $uid = 'cbis-' . $shortKey . '-' . $eventData->Id;
        $isUpdate = false;

        // Check if this is a duplicate or update and if "sync" option is set.
        if ($eventId && get_post_meta($eventId, '_event_manager_uid', true)) {
            $existingUid = get_post_meta($eventId, '_event_manager_uid', true);
            $sync        = get_post_meta($eventId, 'sync', true);
            $postStatus  = get_post_status($eventId);
            $isUpdate    = ($existingUid == $uid && $sync == 1) ? true : false;
        }

        // Save event if it doesn't exist or is an update and "sync" option is set to true. Skips if event is an older duplicate.
        if (($eventId == null || $isUpdate == true) && $this->filter($categories) == true) {
            // Creates the event object
            $event = new Event(
                array(
                    'post_title'              => $newPostTitle,
                    'post_content'            => $postContent,
                    'post_status'             => $postStatus
                ),
                array(
                    '_event_manager_uid'      => 'cbis-' . $shortKey . '-' . $eventData->Id,
                    'sync'                    => 1,
                    'status'                  => isset($eventData->Status) && !empty($eventData->Status) ? $eventData->Status : null,
                    'image'                   => $newImage,
                    'alternate_name'          => isset($eventData->SystemName) && !empty($eventData->SystemName) ? $eventData->SystemName : null,
                    'event_link'              => $this->getAttributeValue(self::ATTRIBUTE_EVENT_LINK, $attributes),
                    'categories'              => $categories,
                    'occasions'               => $occasions,
                    'location'                => !is_null($locationId) ? $locationId : null,
                    'organizers'              => $organizers,
                    'booking_link'            => $this->getAttributeValue(self::ATTRIBUTE_BOOKING_LINK, $attributes),
                    'booking_phone'           => $this->getAttributeValue(self::ATTRIBUTE_BOOKING_PHONE_NUMBER, $attributes),
                    'age_restriction'         => $this->getAttributeValue(self::ATTRIBUTE_AGE_RESTRICTION, $attributes),
                    'price_information'       => $this->getAttributeValue(self::ATTRIBUTE_PRICE_INFORMATION, $attributes),
                    'price_adult'             => $this->getAttributeValue(self::ATTRIBUTE_PRICE_ADULT, $attributes),
                    'price_children'          => $this->getAttributeValue(self::ATTRIBUTE_PRICE_CHILD, $attributes),
                    'import_client'           => 'cbis',
                    'imported_post'           => 1,
                    'user_groups'             => $userGroups,
                    'missing_user_group'      => $userGroups == null ? 1 : 0,
                )
            );

            $creatSuccess = $event->save();
            $eventId = $event->ID;
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
