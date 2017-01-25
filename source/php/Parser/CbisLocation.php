<?php

namespace HbgEventImporter\Parser;

use \HbgEventImporter\Event as Event;
use \HbgEventImporter\Location as Location;
use \HbgEventImporter\Contact as Contact;

class CbisLocation extends \HbgEventImporter\Parser
{
    //API for cbis
    //http://api.cbis.citybreak.com/
    /**
     * Holds the Soap client
     * @var SoapClient
     */
    private $client = null;

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

    //CBIS attribute id's we use
    const ATTRIBUTE_NAME                        =   99;
    const ATTRIBUTE_INGRESS                     =   101;
    const ATTRIBUTE_DESCRIPTION                 =   102;
    const ATTRIBUTE_PRICE_INFORMATION           =   106;
    const ATTRIBUTE_PHONE_NUMBER                =   107;
    const ATTRIBUTE_ORGANIZER_EMAIL             =   109;
    const ATTRIBUTE_WEB_SITE                    =   110;
    const ATTRIBUTE_LATITUDE                    =   113;
    const ATTRIBUTE_LONGITUDE                   =   114;
    const ATTRIBUTE_MEDIA                       =   115;
    const ATTRIBUTE_ADDRESS                     =   117;
    const ATTRIBUTE_POSTCODE                    =   120;
    const ATTRIBUTE_POSTAL_ADDRESS              =   121;
    const ATTRIBUTE_COUNTRY                     =   122;
    const ATTRIBUTE_EVENT_LINK                  =   125;
    const ATTRIBUTE_BOOKING_LINK                =   126;
    const ATTRIBUTE_AGE_RESTRICTION             =   127;
    const ATTRIBUTE_BOOKING_PHONE_NUMBER        =   145;
    const ATTRIBUTE_COUNTRY_CODE                =   147; //Examples I have seen '46', '+46', '046', '0046', '042-183270'
    const ATTRIBUTE_EXTERNAL_LINKS              =   152;
    const ATTRIBUTE_CONTACT_PERSON              =   160;
    const ATTRIBUTE_CONTACT_EMAIL               =   161;
    const ATTRIBUTE_PRICE_CHILD                 =   184;
    const ATTRIBUTE_PRICE_ADULT                 =   191;
    const ATTRIBUTE_CO_ORGANIZER                =   262;
    const ATTRIBUTE_MUNICIPALITY                =   356;
    const ATTRIBUTE_COUNTRY_CODE2               =   556; //Examples I have seen '46', '+46', '046', '0046', '042-107400'

    /**
     * Start the parsing!
     * @return void
     */
    public function start()
    {
        global $wpdb;

        $this->collectDataForLevenshtein();
        $this->client = new \SoapClient($this->url, array('keep_alive' => false));

        // CBIS API keys
        $cbisKey         = $this->apiKeys['cbis_key'];
        $cbisId          = $this->apiKeys['cbis_geonode'];

        $userGroups      = (! empty($this->apiKeys['cbis_groups'])) ? array_map('intval', $this->apiKeys['cbis_groups']) : null;

        // Location data
        $isArena         = $this->cbisLocation['arena'];
        $cbisCategory    = $this->cbisLocation['cbis_location_cat_id'];
        $cbisLocName     = $this->cbisLocation['cbis_location_name'];

        $defaultLocation = get_field('default_city', 'option') ? get_field('default_city', 'option') : null;
        $postStatus      = get_field('cbis_post_status', 'option') ? get_field('cbis_post_status', 'option') : 'publish';

        if (!isset($cbisKey) || empty($cbisKey) || !isset($cbisId) || empty($cbisId)) {
            throw new \Exception('Needed authorization information (CBIS API id and/or CBIS API key) is missing.');
        }

        // Number of arenas/products to get, 500 to get all
        $getLength = 500;

        $requestParams = array(
            'apiKey' => $cbisKey,
            'languageId' => 1,
            'categoryId' => $cbisCategory,
            'templateId' => 0,
            'pageOffset' => 0,
            'itemsPerPage' => $getLength,
            'filter' => array(
                'GeoNodeIds' => array($cbisId),
                'StartDate' => date('c'),
                'Highlights' => 0,
                'OrderBy' => 'Date',
                'SortOrder' => 'Descending',
                'MaxLatitude' => null,
                'MinLatitude' => null,
                'MaxLongitude' => null,
                'MinLongitude' => null,
                'SubCategoryId' => 0,
                'ProductType' => 'Arena',
                'WithOccasionsOnly' => true,
                'ExcludeProductsWithoutOccasions' => true,
                'ExcludeProductsNotInCurrentLanguage' => false,
                'IncludeArchivedProducts' => false,
                'IncludeInactiveProducts' => false,
                'BookableProductsFirst' => false,
                'RandomSortSeed' => 0,
                'ExcludeProductsWhereNameNotInCurrentLanguage' => false,
                'IncludePendingPublish' => false
            )
        );

        if (intval($isArena)) {
            // Get and save event "arenas" to locations
            $this->arenas = $this->client->ListAll($requestParams)->ListAllResult->Items->Product;

            foreach($this->arenas as $key => $arenaData) {
                $this->saveArena($arenaData, $cbisLocName, $defaultLocation, $userGroups);
            }

        } else {
            // Adjust request parameters for getting products
            $requestParams['filter']['ProductType'] = "Product";
            $requestParams['filter']['WithOccasionsOnly'] = false;
            $requestParams['filter']['ExcludeProductsWithoutOccasions'] = false;
            $requestParams['filter']['StartDate'] = null;

            $this->products = $this->client->ListAll($requestParams)->ListAllResult->Items->Product;

            // Filter expired products
            $filteredProducts = array_filter($this->products, function($obj){
                if (isset($obj->ExpirationDate) && strtotime($obj->ExpirationDate) < strtotime("now")) {
                    return false;
                }
                return true;
            });
            foreach ($filteredProducts as $product) {
                $this->saveArena($product, $cbisLocName, $defaultLocation, $userGroups);
            }
        }
    }

    /**
     * Get attributes from location data
     * @param  object $eventData Event data object
     * @return array             Attributes
     */
    public function getAttributes($eventData)
    {
        $attributes = array();

        $dataHolder = $eventData->Attributes->AttributeData;

        if (!is_array($dataHolder)) {
            $dataHolder = array($dataHolder);
        }

        foreach ($dataHolder as $attribute) {
            $attributes[$attribute->AttributeId] = $attribute->Value;
        }

        return $attributes;
    }

    /**
     * Cleans a single locations data into correct format and saves it to db
     * (This function is not the same as the part in saveEvent that looks almost like this, there are no GeoNode when getting arenas from CBIS)
     * @param  object $arenaData       Location data
     * @param  string $productCategory Category name
     * @param  string $defaultLocation Default city
     * @param  array  $userGroups      Default user groups
     * @return void
     */
    public function saveArena($arenaData, $productCategory, $defaultLocation, $userGroups)
    {
        $attributes = $this->getAttributes($arenaData);
        $import_client = 'CBIS: '.ucfirst($productCategory);
        if($this->getAttributeValue(self::ATTRIBUTE_ADDRESS, $attributes) == null && $this->getAttributeValue(self::ATTRIBUTE_NAME, $attributes) == null)
            return;

        $newPostTitle = $this->getAttributeValue(self::ATTRIBUTE_NAME, $attributes) != null ? $this->getAttributeValue(self::ATTRIBUTE_NAME, $attributes) : $this->getAttributeValue(self::ATTRIBUTE_ADDRESS, $attributes);

        // Checking if there is a post already with this title or similar enough
        $locationId = $this->checkIfPostExists('location', $newPostTitle);
        if ($locationId == null) {
            $country = $this->getAttributeValue(self::ATTRIBUTE_COUNTRY, $attributes);
        $arenaLocation = $this->getAttributeValue(self::ATTRIBUTE_POSTAL_ADDRESS, $attributes) != null ? $this->getAttributeValue(self::ATTRIBUTE_POSTAL_ADDRESS, $attributes) : $defaultLocation;
        $city = ($productCategory == 'arena') ? $arenaLocation : $arenaData->GeoNode->Name;

            if(is_numeric($country))
                $country = "Sweden";
            // Create the location, found in api-event-manager/source/php/PostTypes/Locations.php
            $latitude = $this->getAttributeValue(self::ATTRIBUTE_LATITUDE, $attributes) != '0' ? $this->getAttributeValue(self::ATTRIBUTE_LATITUDE, $attributes) : null;
            $longitude = $this->getAttributeValue(self::ATTRIBUTE_LONGITUDE, $attributes) != '0' ? $this->getAttributeValue(self::ATTRIBUTE_LONGITUDE, $attributes) : null;
            $location = new Location(
                array(
                    'post_title' => $newPostTitle
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
                    '_event_manager_uid' => $this->getAttributeValue(self::ATTRIBUTE_NAME, $attributes) ? $this->getAttributeValue(self::ATTRIBUTE_NAME, $attributes) : $this->getAttributeValue(self::ATTRIBUTE_ADDRESS, $attributes),
                    'user_groups'        => $userGroups,
                    'missing_user_group' => $userGroups == null ? 1 : 0,
                )
            );

            $creatSuccess = $location->save();
            $locationId = $location->ID;
            if($creatSuccess)
            {
                ++$this->nrOfNewLocations;
                $this->levenshteinTitles['location'][] = array('ID' => $locationId, 'post_title' => $newPostTitle);
            }
        }
    }

    /**
     * Get attribute value from attribute id
     * @param  integer $attributeId Attribute id
     * @param  array   $attributes  Attribute haystack
     * @param  mixed   $default     Default return value (if nothing is found)
     * @return mixed                Found attribute value else default value or null
     */
    public function getAttributeValue($attributeId, $attributes, $default = null)
    {
        if (isset($attributes[$attributeId]) && !isset($attributes[$attributeId]->Data)) {
            echo "Inside getValue, this should not happen:\n";
            var_dump($attributes[$attributeId]);
        }

        return isset($attributes[$attributeId]) ? $attributes[$attributeId]->Data : $default;
    }
}
