<?php

namespace HbgEventImporter\Parser;

use \HbgEventImporter\Location as Location;

ini_set('memory_limit', '256M');
ini_set('default_socket_timeout', 60 * 10);

class Arcgis extends \HbgEventImporter\Parser
{
    public function __construct($url, $apiKeys)
    {
        parent::__construct($url, $apiKeys);
    }

    /**
     * Get Location data from ArcGIS
     * @return array|void
     */
    private function getLocationData()
    {
        // Skip ssl verification in dev mode
        $args = array(
            'timeout' => 120,
            'sslverify' => defined('DEV_MODE') && DEV_MODE == true ? false : true,
        );
        $request = wp_remote_get($this->url, $args);
        if (is_wp_error($request)) {
            return;
        }

        $body = wp_remote_retrieve_body($request);
        $data = json_decode($body, true);

        if (!$data || !isset($data['features']) || (is_object($data) && $data->code == 'Error')) {
            return;
        }

        return $data['features'];
    }

    /**
     * Start the parsing!
     * @return void
     * @throws \Exception
     */
    public function start()
    {
        $locationData = $this->getLocationData();
        if (!$locationData) {
            return;
        }

        $this->collectDataForLevenshtein();

        // Used to set unique key on locations
        $shortKey = substr(intval($this->url, 36), 0, 4);

        foreach ($locationData as $key => $location) {
            $this->saveLocation($location, $shortKey);
        }
    }

    /**
     * Creates or updates a location if possible
     * @param  array $locationData Location data
     * @param  int   $shortKey     unique key, created from the api url
     * @throws \Exception
     * @return void
     */
    public function saveLocation($locationData, $shortKey)
    {
        if (empty($locationData['attributes']['name']) && empty($locationData['attributes']['address'])) {
            return;
        }

        $address = $locationData['attributes']['address'] ?? null;
        $postTitle = !empty($locationData['attributes']['name']) ? $locationData['attributes']['name'] : $address;
        $userGroups = (is_array($this->apiKeys['arcgis_groups']) && !empty($this->apiKeys['arcgis_groups'])) ? array_map('intval', $this->apiKeys['arcgis_groups']) : null;
        $links = !empty($locationData['attributes']['url']) ? array(array('service' => 'webpage', 'url' => $locationData['attributes']['url'])) : null;
        $locationType = $locationData['attributes']['type'] ?? 'location';
        $uid = 'arcgis-' . $shortKey . '-' . sanitize_title($locationType) . '-' . sanitize_title($postTitle);
        $postStatus = get_field('arcgis_post_status', 'option') ? get_field('arcgis_post_status', 'option') : 'publish';
        // Checking if there is a location already with this title or similar enough
        $locationId = $this->checkIfPostExists('location', $postTitle);

        $isUpdate = false;
        // Check if this is a duplicate or update and if "sync" option is set.
        if ($locationId && get_post_meta($locationId, '_event_manager_uid', true)) {
            $existingUid = get_post_meta($locationId, '_event_manager_uid', true);
            $sync = get_post_meta($locationId, 'sync', true);
            $postStatus = get_post_status($locationId);

            if ($existingUid == $uid && $sync == 1) {
                $isUpdate = true;
            }
        }

        // Return if location already exist and api sync is disabled
        if ($locationId && !$isUpdate) {
            return;
        }

        // Create the location
        try {
            $location = new Location(
                array(
                    'post_title' => $postTitle,
                    'post_status' => $postStatus,
                ),
                array(
                    'street_address' => $address,
                    'postal_code' => null,
                    'city' => $this->apiKeys['default_city'],
                    'municipality' => null,
                    'country' => null,
                    'latitude' => $locationData['geometry']['y'] ?? null,
                    'longitude' => $locationData['geometry']['x'] ?? null,
                    'import_client' => 'ArcGIS',
                    '_event_manager_uid' => $uid,
                    'user_groups' => $userGroups,
                    'sync' => 1,
                    'imported_post' => 1,
                    'links' => $links
                )
            );
        } catch (\Exception $e) {
            error_log(print_r($e, true));
            return;
        }

        if ($location->save()) {
            if ($isUpdate == false) {
                ++$this->nrOfNewLocations;
            }

            $this->levenshteinTitles['location'][] = array('ID' => $location->ID, 'post_title' => $postTitle);
        }
    }
}
