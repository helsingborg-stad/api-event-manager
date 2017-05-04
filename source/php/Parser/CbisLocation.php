<?php

namespace HbgEventImporter\Parser;

use \HbgEventImporter\Event as Event;
use \HbgEventImporter\Location as Location;
use \HbgEventImporter\Contact as Contact;

class CbisLocation extends \HbgEventImporter\Parser\Cbis
{
    /**
     * Holds a list of all found arenas
     * @var array
     */
    private $arenas = array();

    /**
     * Holds a list of all found products
     * @var array
     */
    private $products = array();

    /**
     * Start the parsing!
     * @return void
     */
    public function start()
    {
        global $wpdb;

        $this->collectDataForLevenshtein();

        $requestParams = array();

        // CBIS API keys
        $cbisKey         = $this->apiKeys['cbis_key'];
        $cbisId          = $this->apiKeys['cbis_geonode'];
        $userGroups      = (is_array($this->apiKeys['cbis_groups']) && ! empty($this->apiKeys['cbis_groups'])) ? array_map('intval', $this->apiKeys['cbis_groups']) : null;

        // Used to set unique key on events
        $shortKey        = substr(intval($this->apiKeys['cbis_key'], 36), 0, 4);

        // Location data
        $isArena         = $this->cbisLocation['arena'];
        $cbisCategory    = $this->cbisLocation['cbis_location_cat_id'];
        $cbisLocName     = $this->cbisLocation['cbis_location_name'];

        $defaultLocation = get_field('default_city', 'option') ? get_field('default_city', 'option') : null;
        $postStatus      = get_field('cbis_post_status', 'option') ? get_field('cbis_post_status', 'option') : 'publish';

        // Number of arenas/products to get, 500 to get all
        $requestParams['itemsPerPage'] = 600;
        $requestParams['filter']['StartDate'] = null;

        if (intval($isArena)) {
            $requestParams['filter']['ProductType'] = "Arena";

            // Get and save event "arenas" to locations
            $response = $this->soapRequest($cbisKey, $cbisId, $cbisCategory, $requestParams);
            $this->arenas = $response->ListAllResult->Items->Product;

            wp_die(count($this->arenas));

            foreach ($this->arenas as $arena) {
                $this->saveLocation($arena, 'arena', $defaultLocation, $userGroups, $shortKey, $postStatus);
            }
        } else {
            // Adjust request parameters when parsing products
            $requestParams['filter']['ProductType'] = "Product";
            $requestParams['filter']['WithOccasionsOnly'] = false;
            $requestParams['filter']['ExcludeProductsWithoutOccasions'] = false;

            $response = $this->soapRequest($cbisKey, $cbisId, $cbisCategory, $requestParams);
            $this->products = $response->ListAllResult->Items->Product;

            // Filter expired products
            $filteredProducts = array_filter($this->products, function ($obj) {
                if (isset($obj->ExpirationDate) && strtotime($obj->ExpirationDate) < strtotime("now")) {
                    return false;
                }

                return true;
            });

            foreach ($filteredProducts as $product) {
                $this->saveLocation($product, $cbisLocName, $defaultLocation, $userGroups, $shortKey, $postStatus);
            }
        }
    }

    /**
     * Cleans a single locations data into correct format and saves it to db
     * (This function is not the same as the part in saveEvent that looks almost like this, there are no GeoNode when getting arenas from CBIS)
     * @param  object $arenaData       Location data
     * @param  string $productCategory Category name
     * @param  string $defaultLocation Default city
     * @param  array  $userGroups      Default user groups
     * @param  int    $shortKey        Shortened api key
     * @return void
     */
    public function saveLocation($arenaData, $productCategory, $defaultLocation, $userGroups, $shortKey, $postStatus)
    {
        $attributes = $this->getAttributes($arenaData);
        $import_client = 'CBIS: '.ucfirst($productCategory);
        if ($this->getAttributeValue(self::ATTRIBUTE_ADDRESS, $attributes) == null && $this->getAttributeValue(self::ATTRIBUTE_NAME, $attributes) == null) {
            return;
        }

        $newPostTitle = $this->getAttributeValue(self::ATTRIBUTE_NAME, $attributes) != null ? $this->getAttributeValue(self::ATTRIBUTE_NAME, $attributes) : $this->getAttributeValue(self::ATTRIBUTE_ADDRESS, $attributes);

        // Checking if there is a post already with this title or similar enough
        $locationId = $this->checkIfPostExists('location', $newPostTitle);

        $uid = 'cbis-' . $shortKey . '-' . $this->cleanString($newPostTitle);
        $isUpdate = false;

        // Check if this is a duplicate or update and if "sync" option is set.
        if ($locationId && get_post_meta($locationId, '_event_manager_uid', true)) {
            $existingUid   = get_post_meta($locationId, '_event_manager_uid', true);
            $sync          = get_post_meta($locationId, 'sync', true);
            $postStatus    = get_post_status($locationId);
            $isUpdate      = ($existingUid == $uid && $sync == 1) ? true : false;
        }

        if ($locationId == null || $isUpdate == true) {
            $country = $this->getAttributeValue(self::ATTRIBUTE_COUNTRY, $attributes);
            $arenaLocation = $this->getAttributeValue(self::ATTRIBUTE_POSTAL_ADDRESS, $attributes) != null ? $this->getAttributeValue(self::ATTRIBUTE_POSTAL_ADDRESS, $attributes) : $defaultLocation;
            $city = ($productCategory == 'arena') ? $arenaLocation : $arenaData->GeoNode->Name;

            if (is_numeric($country)) {
                $country = "Sweden";
            }
            // Create the location
            $latitude = $this->getAttributeValue(self::ATTRIBUTE_LATITUDE, $attributes) != '0' ? $this->getAttributeValue(self::ATTRIBUTE_LATITUDE, $attributes) : null;
            $longitude = $this->getAttributeValue(self::ATTRIBUTE_LONGITUDE, $attributes) != '0' ? $this->getAttributeValue(self::ATTRIBUTE_LONGITUDE, $attributes) : null;
            $location = new Location(
                array(
                    'post_title'         => $newPostTitle,
                    'post_status'        => $postStatus,
                ),
                array(
                    'street_address'     => $this->getAttributeValue(self::ATTRIBUTE_ADDRESS, $attributes),
                    'postal_code'        => $this->getAttributeValue(self::ATTRIBUTE_POSTCODE, $attributes),
                    'city'               => $city,
                    'municipality'       => $this->getAttributeValue(self::ATTRIBUTE_MUNICIPALITY, $attributes),
                    'country'            => $country,
                    'latitude'           => $latitude,
                    'longitude'          => $longitude,
                    'import_client'      => $import_client,
                    '_event_manager_uid' => $uid,
                    'user_groups'        => $userGroups,
                    'missing_user_group' => $userGroups == null ? 1 : 0,
                    'sync'               => 1,
                    'imported_post'      => 1,
                )
            );

            $createSuccess = $location->save();
            $locationId = $location->ID;

            if ($createSuccess) {
                if ($isUpdate == false) {
                    ++$this->nrOfNewLocations;
                }

                $this->levenshteinTitles['location'][] = array('ID' => $locationId, 'post_title' => $newPostTitle);
            }
        }
    }
}
