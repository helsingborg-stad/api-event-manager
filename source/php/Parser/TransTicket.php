<?php

namespace HbgEventImporter\Parser;

use \HbgEventImporter\Event as Event;
use \HbgEventImporter\Location as Location;
use \HbgEventImporter\Helper\Address as Address;

ini_set('memory_limit', '256M');
ini_set('default_socket_timeout', 60 * 10);

class TransTicket extends \HbgEventImporter\Parser
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
        $endImportDate = ($this->apiKeys['transticket_weeks'] !== '') ? date("Y-m-d",
            strtotime("+" . $this->apiKeys['transticket_weeks'] . " weeks")) : date("Y-m-d", strtotime("+1 weeks"));
        $url = $this->url . '&FromDate=' . date("Y-m-d") . '&ToDate=' . $endImportDate;
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
        $shortKey = substr(md5($this->url), 0, 8);

        foreach ($eventData as $key => $event) {
            if (!isset($event->Id) || empty($event->Id)) {
                continue;
            }

            //Remove everything that hasen't been published to web
            if (strtotime($event->ReleaseDate) > time()) {
                continue;
            }

            //Check bitmask value for Point of sale
            if (($event->POS & 2) != 2) {
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
        $data['postContent'] = isset($eventData->ExtendedDescription) && !empty($eventData->ExtendedDescription) ? strip_tags($eventData->ExtendedDescription, '<a><p><br>') : '';
        $data['uId'] = $eventData->Id;
        $data['booking_link'] = add_query_arg( array(
            'qry' => urlencode($data['postTitle']),
        ), trim($this->apiKeys['transticket_ticket_url'], '/') . "/Find/" );
        $data['startDate'] = !empty($eventData->EventDate) ? $eventData->EventDate : null;
        $data['endDate'] = !empty($eventData->EndDate) ? $eventData->EndDate : null;
        if ($data['endDate'] === null) {
            $data['endDate'] = date("Y-m-d H:i:s", strtotime($data['startDate'] . "+1 hour"));
            $data['endDate'] = str_replace(' ', 'T', $data['endDate']);
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
        $data['postStatus'] = get_field('transticket_post_status', 'option') ? get_field('transticket_post_status',
            'option') : 'publish';
        $data['userGroups'] = (is_array($this->apiKeys['transticket_groups']) && !empty($this->apiKeys['transticket_groups'])) ? array_map('intval',
            $this->apiKeys['transticket_groups']) : null;

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

        // Get price range, if data is missing get data from custom ticket types
        $data['price_range_seated_minimum_price'] = $eventData->Prices->SeatedMinPrice ?? null;
        if ($data['price_range_seated_minimum_price'] === null && !empty($data['additional_ticket_types'])) {
            $seated = array_filter($data['additional_ticket_types'], function ($a) {
                return in_array('Seated', $a) && isset($a['minimum_price']) && $a['minimum_price'] !== '';
            });
            $data['price_range_seated_minimum_price'] = (!empty($seated)) ? min(array_column($seated, 'minimum_price')) : null;
        }
        $data['price_range_seated_maximum_price'] = $eventData->Prices->SeatedMaxPrice ?? null;
        if ($data['price_range_seated_maximum_price'] === null && !empty($data['additional_ticket_types'])) {
            $seated = array_filter($data['additional_ticket_types'], function ($a) {
                return in_array('Seated', $a) && isset($a['maximum_price']) && $a['maximum_price'] !== '';
            });
            $data['price_range_seated_maximum_price'] = (!empty($seated)) ? max(array_column($seated, 'maximum_price')) : null;
        }
        $data['price_range_standing_minimum_price'] = $eventData->Prices->StandingMinPrice ?? null;
        if ($data['price_range_standing_minimum_price'] === null && !empty($data['additional_ticket_types'])) {
            $seated = array_filter($data['additional_ticket_types'], function ($a) {
                return in_array('Standing', $a) && isset($a['minimum_price']) && $a['minimum_price'] !== '';
            });
            $data['price_range_standing_minimum_price'] = (!empty($seated)) ? min(array_column($seated, 'minimum_price')) : null;
        }
        $data['price_range_standing_maximum_price'] = $eventData->Prices->StandingMaxPrice ?? null;
        if ($data['price_range_standing_maximum_price'] === null && !empty($data['additional_ticket_types'])) {
            $seated = array_filter($data['additional_ticket_types'], function ($a) {
                return in_array('Standing', $a) && isset($a['maximum_price']) && $a['maximum_price'] !== '';
            });
            $data['price_range_standing_maximum_price'] = (!empty($seated)) ? max(array_column($seated, 'maximum_price')) : null;
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

        $locationData = json_decode(json_encode($eventData->VenueExtensions), true);
        if (!empty($locationData)) {
            foreach ($locationData as $key => $venueExtension) {
                if (!empty($venueExtension['Key'])) {
                    switch ($venueExtension['Key']) {
                        case "Address":
                            $data['address'] = $venueExtension['Value'];
                            break;
                        case "CoordinateX":
                            $data['longitude'] = $venueExtension['Value'];
                            break;
                        case "CoordinateY":
                            $data['latitude'] = $venueExtension['Value'];
                            break;
                    }
                }
            }
        }

        $data['name'] = !empty($eventData->VenueName) ? $eventData->VenueName : null;
        $data['city'] = !empty($eventData->VenueCity) ? $eventData->VenueCity : $this->apiKeys['default_city'];

        $locationId = $this->maybeCreateLocation($data, $shortKey);
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

        $eventManagerUid = (get_post_meta($eventId, '_event_manager_uid', true)) ? get_post_meta($eventId,
            '_event_manager_uid', true) : 'transticket-' . $shortKey . '-' . $data['uId'];
        $postStatus = $data['postStatus'];
        // Get existing event meta data
        $sync = true;
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
                    'user_groups' => $data['userGroups'],
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

        //Add & remove tags
        wp_set_post_terms(
            $event->ID,
            $data['categories'],
            'event_tags',
            false
        );

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
        $uid = 'transticket-' . $shortKey . '-' . $this->cleanString($postTitle);

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
                    'import_client' => 'transticket',
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
}
