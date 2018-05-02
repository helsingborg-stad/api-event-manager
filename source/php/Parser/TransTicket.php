<?php

namespace HbgEventImporter\Parser;

use \HbgEventImporter\Event as Event;
use \HbgEventImporter\Location as Location;
use \HbgEventImporter\Helper\Address as Address;

class TransTicket extends \HbgEventImporter\Parser
{
    public function __construct($url, $apiKeys)
    {
        error_log(print_r($apiKeys, true));
        parent::__construct($url, $apiKeys);
    }

    /**
     * Get Event data from TransTicket
     * @return array data
     */
    private function getEventData()
    {
        $url = $this->url . '&FromDate=' . date("Y-m-d") . '&ToDate=' . date("Y-m-d", strtotime("+1 weeks"));
        return json_decode(\HbgEventImporter\Helper\Curl::request('GET', $url, $this->apiKeys['transticket_api_key'],
            false, 'json', array('Content-Type: application/json')));
    }

    /**
     * Start the parsing!
     * @return void
     */
    public function start()
    {
        $eventData = $this->getEventData();
        $this->collectDataForLevenshtein();

        // Used to set unique key on events
        $shortKey = substr(intval($this->url, 36), 0, 4);

        foreach ($eventData as $key => $event) {
            if (!isset($event->Id) || empty($event->Id)) {
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
        $data['postTitle'] = strip_tags(isset($eventData->Name) && !empty($eventData->Name) ? $eventData->Name : null);
        error_log($data['postTitle']);
        $data['postContent'] = strip_tags(isset($eventData->Description) && !empty($eventData->Description) ? $eventData->Description : '',
            '<p><br>');

        // TODO Fixa location
        //$data['location'] = $this->findWordWithCapLetters($data['postContent']);
        //echo $data['location'];

        $data['uId'] = $eventData->Id;
        $data['booking_link'] = $this->apiKeys['transticket_ticket_url'] . "/" . $data['uId'] . "/false";

        $data['startDate'] = !empty($eventData->EventDate) ? $eventData->EventDate : null;
        $data['endDate'] = !empty($eventData->EndDate) ? $eventData->EndDate : null;
        if ($data['endDate'] === null) {
            $data['endDate'] = date("Y-m-d H:i:s", strtotime($data['startDate'] . "+1 hour"));
        }

        $data['occasions'] = array();
        if ($data['startDate'] != null && $data['endDate'] != null) {
            $data['occasions'][] = array(
                'start_date' => $data['startDate'],
                'end_date' => $data['endDate'],
                'door_time' => $data['startDate']
            );
        }

        $data['categories'] = isset($eventData->Tags) && is_array($eventData->Tags) ? $this->getCategories($eventData->Tags) : array();
        $data['postStatus'] = get_field('transticket_post_status', 'option') ? get_field('transticket_post_status', 'option') : 'publish';
        $data['user_groups'] = (is_array($this->apiKeys['transticket_groups']) && !empty($this->apiKeys['transticket_groups'])) ? array_map('intval', $this->apiKeys['transticket_groups']) : null;

        $data['image'] = null;
        if (isset($eventData->{'ImageURL'}) && !empty($eventData->{'ImageURL'}) && $eventData->{'ImageURL'} != 'null') {
            $data['image'] = $eventData->{'ImageURL'};
        }

        if (!is_string($data['postTitle'])) {
            return;
        }

        // Various data vars not in default setup
        $data['ticket_stock'] = $eventData->Stock ?? null;
        $data['ticket_release_date'] = !empty($eventData->ReleaseDate) ? $eventData->ReleaseDate : null;
        $data['tickets_remaining'] = $eventData->Sales->RemainingTickets ?? null;

        $data['price_range_seated_minimum_price'] = $eventData->Prices->SeatedMinPrice ?? null;
        $data['price_range_seated_maximum_price'] = $eventData->Prices->SeatedMaxPrice ?? null;
        $data['price_range_standing_minimum_price'] = $eventData->Prices->StandingMinPrice ?? null;
        $data['price_range_standing_maximum_price'] = $eventData->Prices->StandingMaxPrice ?? null;

        $data['additional_ticket_types'] = array();
        if (isset($eventData->TicketPrices) && !empty($eventData->TicketPrices)) {
            foreach ($eventData->TicketPrices as $price) {
                $data['additional_ticket_types'][] = array(
                    'ticket_name' => isset($price->TicketName) && !empty($price->TicketName) ? $price->TicketName : null,
                    'minimum_price' => $price->MinPrice ?? null,
                    'maximum_price' => $price->MaxPrice ?? null,
                    'ticket_type' => !empty($price->IsSeated) ? 'Seated' : 'Standing'
                );
            }
        }

        $data['additional_ticket_retailers'] = array();
        if (isset($eventData->ReleaseDates) && !empty($eventData->ReleaseDates)) {
            foreach ($eventData->ReleaseDates as $retailer) {
                $data['additional_ticket_retailers'][] = array(
                    'retailer_name' => $retailer->PointOfSale ?? null,
                    'booking_url' => null,
                    'ticket_start_date' => $retailer->StartDate ?? null,
                    'ticket_stop_date' => $retailer->StopDate ?? null
                );
            }
        }

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

        $eventManagerUid = (get_post_meta($eventId, '_event_manager_uid', true)) ? get_post_meta($eventId, '_event_manager_uid', true) : 'transticket-' . $shortKey . '-' . $data['uId'];
        $postStatus = $data['postStatus'];
        // Get existing event meta data
        if ($eventId) {
            $sync = get_post_meta($eventId, 'sync', true);
            $postStatus = get_post_status($eventId);
            $levenshteinKey = array_search($eventId, array_column($this->levenshteinTitles['event'], 'ID'));
            $occurred = $this->levenshteinTitles['event'][$levenshteinKey]['occurred'];
        }

        if (($eventId && !$sync) || !$this->filter($data['categories'])) {
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
                    'image' => !empty($data['image']) ? $data['image'] : null,
                    'event_link' => null,
                    'categories' => $data['categories'],
                    'occasions' => $data['occasions'],
                    'location' => $locationId != null ? $locationId : null,
                    'organizer' => null,
                    'booking_link' => is_string($data['booking_link']) ? $data['booking_link'] : null,
                    'booking_phone' => null,
                    'age_restriction' => null,
                    'price_information' => null,
                    'price_adult' => null,
                    'price_children' => null,
                    'import_client' => 'transticket',
                    'imported_post' => 1,
                    'user_groups' => $data['user_groups'],
                    'occurred' => $occurred,
                    'ticket_stock' => $data['ticket_stock'],
                    'ticket_release_date' => $data['ticket_release_date'],
                    'tickets_remaining' => $data['tickets_remaining'],
                    'additional_ticket_retailers' => $data['additional_ticket_retailers'],
                    'price_range_seated_minimum_price' => $data['price_range_seated_minimum_price'],
                    'price_range_seated_maximum_price' => $data['price_range_seated_maximum_price'],
                    'price_range_standing_minimum_price' => $data['price_range_standing_minimum_price'],
                    'price_range_standing_maximum_price' => $data['price_range_standing_maximum_price'],
                    'additional_ticket_types' => $data['additional_ticket_types'],
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
            $this->nrOfNewEvents++;
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
     * @param  array $data Event data
     * @return boolean|int  Location id or false
     * @throws \Exception
     */
    public function maybeCreateLocation($data, $shortKey)
    {
        if (!is_string($data['address'])) {
            return false;
        }

        // Checking if there is a location already with this title or similar enough
        $locationId = $this->checkIfPostExists('location', $data['address']);

        $locPostStatus = $data['postStatus'];
        $isUpdate = false;

        // Check if this is a duplicate or update and if "sync" option is set.
        if ($locationId && get_post_meta($locationId, '_event_manager_uid', true)) {
            $existingUid = get_post_meta($locationId, '_event_manager_uid', true);
            $sync = get_post_meta($locationId, 'sync', true);
            $locPostStatus = get_post_status($locationId);

            if ($existingUid == 'transticket-' . $shortKey . '-' . $this->cleanString($data['address']) && $sync == 1) {
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
                    'post_title' => $data['address'],
                    'post_status' => $locPostStatus,
                ),
                array(
                    'street_address' => null,
                    'postal_code' => null,
                    //'city' => $data['city'],
                    'city' => $data['VenueId'],
                    'municipality' => null,
                    'country' => null,
                    'latitude' => null,
                    'longitude' => null,
                    'import_client' => 'transticket',
                    '_event_manager_uid' => 'transticket-' . $shortKey . '-' . $this->cleanString($data['address']),
                    'user_groups' => $data['user_groups'],
                    'sync' => 1,
                    'imported_post' => 1,
                )
            );
        } catch (\Exception $e) {
            $location = false;
            error_log($e);
        }

        if (!$location->save()) {
            return false;
        }

        if (!isset($location->duplicate)) {
            $this->nrOfNewLocations++;
        }

        $this->levenshteinTitles['location'][] = array('ID' => $location->ID, 'post_title' => $data['address']);

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
        $exclude = $this->apiKeys['transticket_filter_tags'];

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
     * Get categories from event data
     * @param  object $eventData Event data object
     * @return array             Categories
     */
    public function getCategories($eventCategories)
    {
        $categories = array();

        foreach ($eventCategories as $category) {
            $categories[] = $category->Name;
        }

        $categories = array_map('trim', $categories);
        $categories = array_map('ucwords', $categories);

        return $categories;
    }

    /**
     * Washing string from capitalized words. Add capital letter to first word in sentence.
     * @param  string
     * @return string
     */
    public function ucFirstWordInSentence($str)
    {
        $str = ucfirst(strtolower($str));
        return preg_replace("/([.!?]\s*\w)/e", "strtoupper('$1')", $str);
    }

    /**
     * Find word with capital letters
     * @param  string
     * @return string
     */
    public function findWordWithCapLetters($str)
    {
        if (preg_match_all('/\\b(?=[A-Z])[A-Z ]+(?=\\W)/', $str, $match)) {
            return $match[0];
        }
    }
}
