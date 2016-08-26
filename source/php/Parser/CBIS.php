<?php

namespace HbgEventImporter\Parser;

use \HbgEventImporter\Event as Event;
use \HbgEventImporter\Location as Location;
use \HbgEventImporter\Contact as Contact;

class CBIS extends \HbgEventImporter\Parser
{
    /**
     * Holds the Soap client
     * @var SoapClient
     */
    private $client = null;

    /**
     * Which product type to get
     * @var string Product|Arena
     */
    private $productType = 'Arena';

    /**
     * Holds a list of all found events
     * @var array
     */
    private $events = array();

    /**
     * Holds a list of all found arenas
     * @var array
     */
    private $arenas = array();

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

    //CBIS attributes we need to decide if we want to use
    const ATTRIBUTE_BUSINESS_HOURS              =   104;
    const ATTRIBUTE_NUMBER_OF_ROOMS             =   130;
    const ATTRIBUTE_SPECIAL_THEME               =   155;
    const ATTRIBUTE_NOTE                        =   162;
    const ATTRIBUTE_FACILITIES                  =   163; //This is just a bunch of booleans
    const ATTRIBUTE_LINK_TO_WHITE_GUIDE         =   178;
    const ATTRIBUTE_PRICE_CHILD_UNDER_12        =   185;
    const ATTRIBUTE_PRICE_CHILD_UNDER_7         =   187;
    const ATTRIBUTE_PRICE_STUDENT               =   192;
    const ATTRIBUTE_OUR_MALMO                   =   231;
    const ATTRIBUTE_MALMO_DOT_SE                =   232;
    const ATTRIBUTE_MALMO_TOWN_DOT_COM          =   233;
    const ATTRIBUTE_GETS_CULTURE_SUPPORT        =   263;
    const ATTRIBUTE_NAME_LINK2                  =   282;
    const ATTRIBUTE_HOTEL_CHAIN                 =   315;
    const ATTRIBUTE_FACILTY_PARKING             =   353;
    const ATTRIBUTE_CERTIFICATION               =   354;
    const ATTRIBUTE_AREA                        =   355;
    const ATTRIBUTE_NUMBER_OF_BEDS              =   372;
    const ATTRIBUTE_LEISURE_FACILITIES          =   401;
    const ATTRIBUTE_FOOD_AND_DRINK              =   417;
    const ATTRIBUTE_FACEBOOK_HIGHLIGHTS         =   555;
    const ATTRIBUTE_BROCHURE_TEXT               =   578;
    const ATTRIBUTE_LINK_TO_DOCUMENT            =   579;
    const ATTRIBUTE_CAMPAIGN                    =   609;
    const ATTRIBUTE_MAP_PRIORITY                =   924;
    const ATTRIBUTE_META_KEYWORDS               =   952;
    const ATTRIBUTE_COMPANY_NAME                =   963;

    //CBIS attribute id's we chose not to use
    const ATTRIBUTE_DIRECTIONS                  =   103;
    const ATTRIBUTE_ORGANIZER                   =   261;
    const ATTRIBUTE_EXTERNAL_BOOKING_LINK       =   283;
    const ATTRIBUTE_ZOOM_LEVEL                  =   297;
    const ATTRIBUTE_CURRENCY                    =   402;
    const ATTRIBUTE_SPECIAL_NEEDS               =   418; //This is just a bunch of booleans
    const ATTRIBUTE_EVENT_HIGHLIGHT             =   526;
    const ATTRIBUTE_HIGHLIGHT_FULLSCREEN_MAP    =   550;
    const ATTRIBUTE_EMAIL_OTHER                 =   568;
    const ATTRIBUTE_YOUTUBE_LINK                =   577;
    const ATTRIBUTE_LOCAL                       =   668; //This is just a bunch of booleans
    const ATTRIBUTE_EXTERNAL_BOOKING_LINK2      =   671;
    const ATTRIBUTE_GLOBAL                      =   908; //This is just a bunch of booleans
    const ATTRIBUTE_WHITE_GUIDE                 =   982;

    /**
     * Start the parsing!
     * @return void
     */
    public function start()
    {
        global $wpdb;
        $sql = 'CREATE TABLE IF NOT EXISTS event_occasions(
        ID BIGINT(20) UNSIGNED NOT NULL AUTO_INCREMENT,
        event BIGINT(20) UNSIGNED NOT NULL,
        timestamp BIGINT(20) UNSIGNED NOT NULL,
        PRIMARY KEY (ID))';

        $wpdb->get_results($sql);

        $this->collectDataForLevenshtein();

        $this->client = new \SoapClient($this->url, array('keep_alive' => false));

        $cbisKey = get_option('options_cbis_api_key');
        $cbisId = intval(get_option('options_cbis_api_id'));
        $cbisCategory = 14086;

        if (!isset($cbisKey) || empty($cbisKey) || !isset($cbisId) || empty($cbisId)) {
            throw new \Exception('Needed authorization information (CBIS API id and/or CBIS API key) is missing.');
        }

        // Number of arenas to get, 200 to get all
        $getLength = 200;

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
                'ProductType' => $this->productType,
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

        // Get and save the arenas
        $this->arenas = $this->client->ListAll($requestParams)->ListAllResult->Items->Product;

        foreach($this->arenas as $key => $arenaData) {
            $this->saveArena($arenaData);
        }

        // Adjust request parameters for getting products, 1500 itemsPerPage to get all events
        $requestParams['filter']['ProductType'] = "Product";
        $requestParams['itemsPerPage'] = 1500;

        // Get and save the events
        $this->events = $this->client->ListAll($requestParams)->ListAllResult->Items->Product;

        foreach ($this->events as $eventData) {
            $this->saveEvent($eventData);
        }
    }

    /**
     * Get attributes from event data
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
     * @return array            Occasions
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
            }

            if (isset($occasion->EntryTime)) {
                $doorTime = explode('T', $occasion->StartDate)[0] . 'T' . explode('T', $occasion->EntryTime)[1];
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
     * Cleans a single locations data into correct format and saves it to db
     * @param  object $arenaData  Location data
     * @return void
     */
    //This function is not the same as the part in saveEvent that looks almost like this, there are no GeoNode when getting arenas from CBIS
    public function saveArena($arenaData)
    {
        $attributes = $this->getAttributes($arenaData);

        if($this->getAttributeValue(self::ATTRIBUTE_ADDRESS, $attributes) == null && $this->getAttributeValue(self::ATTRIBUTE_NAME, $attributes) == null)
            return;

        $newPostTitle = $this->getAttributeValue(self::ATTRIBUTE_ADDRESS, $attributes) != null ? $this->getAttributeValue(self::ATTRIBUTE_ADDRESS, $attributes) : $this->getAttributeValue(self::ATTRIBUTE_NAME, $attributes);

        // Checking if there is a post already with this title or similar enough
        $locationId = $this->checkIfPostExists('location', $newPostTitle);
        if($locationId == null)
        {
            $country = $this->getAttributeValue(self::ATTRIBUTE_COUNTRY, $attributes);
            if(is_numeric($country))
                $country = "Sweden";
            // Create the location
            $location = new Location(
                array(
                    'post_title' => $newPostTitle
                ),
                array(
                    'street_address'     => $this->getAttributeValue(self::ATTRIBUTE_ADDRESS, $attributes),
                    'postal_code'        => $this->getAttributeValue(self::ATTRIBUTE_POSTCODE, $attributes),
                    'city'               => $this->getAttributeValue(self::ATTRIBUTE_POSTAL_ADDRESS, $attributes),
                    'municipality'       => $this->getAttributeValue(self::ATTRIBUTE_MUNICIPALITY, $attributes),
                    'country'            => $country,
                    'latitude'           => $this->getAttributeValue(self::ATTRIBUTE_LATITUDE, $attributes),
                    'longitude'          => $this->getAttributeValue(self::ATTRIBUTE_LONGITUDE, $attributes),

                    '_event_manager_uid' => $this->getAttributeValue(self::ATTRIBUTE_ADDRESS, $attributes) ? $this->getAttributeValue(self::ATTRIBUTE_ADDRESS, $attributes) : $this->getAttributeValue(self::ATTRIBUTE_NAME, $attributes)
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
     * Cleans a single events data into correct format and saves it to db
     * @param  object $eventData  Event data
     * @return void
     */
    public function saveEvent($eventData)
    {
        $attributes = $this->getAttributes($eventData);
        $categories = $this->getCategories($eventData);
        $occasions = $this->getOccasions($eventData);

        $newPostTitle = $this->getAttributeValue(self::ATTRIBUTE_ADDRESS, $attributes) ? $this->getAttributeValue(self::ATTRIBUTE_ADDRESS, $attributes) : $eventData->GeoNode->Name;

        $locationId = $this->checkIfPostExists('location', $newPostTitle);
        if ($locationId == null) {

            $country = $this->getAttributeValue(self::ATTRIBUTE_COUNTRY, $attributes);
            if(is_numeric($country))
                $country = "Sweden";

            // Create the location
            $location = new Location(
                array(
                    'post_title'            =>  $newPostTitle
                ),
                array(
                    'street_address'        =>  $this->getAttributeValue(self::ATTRIBUTE_ADDRESS, $attributes),
                    'postal_code'           =>  $this->getAttributeValue(self::ATTRIBUTE_POSTCODE, $attributes),
                    'city'                  =>  $eventData->GeoNode->Name,
                    'municipality'          =>  $this->getAttributeValue(self::ATTRIBUTE_MUNICIPALITY, $attributes),
                    'country'               =>  $country,
                    'latitude'              =>  $this->getAttributeValue(self::ATTRIBUTE_LATITUDE, $attributes),
                    'longitude'             =>  $this->getAttributeValue(self::ATTRIBUTE_LONGITUDE, $attributes),

                    '_event_manager_uid'    =>  $this->getAttributeValue(self::ATTRIBUTE_ADDRESS, $attributes) ? $this->getAttributeValue(self::ATTRIBUTE_ADDRESS, $attributes) : $eventData->GeoNode->Name
                )
            );

            $creatSuccess = $location->save();
            $locationId = $location->ID;
            if($creatSuccess)
            {
                ++$this->nrOfNewLocations;
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
            if ($contactId == null) {
                $phoneNumber = $this->getAttributeValue(self::ATTRIBUTE_PHONE_NUMBER, $attributes);
                // Save contact
                $contact = new Contact(
                    array(
                        'post_title'            =>  $newPostTitle
                    ),
                    array(
                        'name'                  =>  $this->getAttributeValue(self::ATTRIBUTE_CONTACT_PERSON, $attributes),
                        'email'                 =>  strtolower($this->getAttributeValue(self::ATTRIBUTE_CONTACT_EMAIL, $attributes)),
                        'phone_number'          =>  $phoneNumber == null ? $phoneNumber : (strlen($phoneNumber) > 5 ? $phoneNumber : null),
                        '_event_manager_uid'    =>  $this->getAttributeValue(self::ATTRIBUTE_CONTACT_PERSON, $attributes) . ': ' . strtolower($this->getAttributeValue(self::ATTRIBUTE_CONTACT_EMAIL, $attributes))
                    )
                );

                $creatSuccess = $contact->save();
                $contactId = $contact->ID;
                if($creatSuccess)
                {
                    ++$this->nrOfNewContacts;
                    $this->levenshteinTitles['contact'][] = array('ID' => $contact->ID, 'post_title' => $newPostTitle);
                }
            }
        }

        $postContent = $this->getAttributeValue(self::ATTRIBUTE_DESCRIPTION, $attributes);
        if ($this->getAttributeValue(self::ATTRIBUTE_INGRESS, $attributes) && !empty($this->getAttributeValue(self::ATTRIBUTE_INGRESS, $attributes))) {
            $postContent = $this->getAttributeValue(self::ATTRIBUTE_INGRESS, $attributes) . "<!--more-->\n\n" . $postContent;
        }

        $newPostTitle = $this->getAttributeValue(self::ATTRIBUTE_NAME, $attributes, ($eventData->Name != null ? $eventData->Name : null));

        $newImage = (isset($eventData->Image->Url) ? $eventData->Image->Url : null);
        $eventId = $this->checkIfPostExists('event', $newPostTitle);
        if ($eventId == null) {
            // Creates the event object
            $event = new Event(
                array(
                    'post_title'            => $newPostTitle,
                    'post_content'          => $postContent
                ),
                array(
                    'uniqueId'              => 'cbis-' . $eventData->Id,
                    '_event_manager_uid'    => 'cbis-' . $eventData->Id,
                    'sync'                  => true,
                    'status'                => isset($eventData->Status) && !empty($eventData->Status) ? $eventData->Status : null,
                    'image'                 => $newImage,
                    'alternate_name'        => isset($eventData->SystemName) && !empty($eventData->SystemName) ? $eventData->SystemName : null,
                    'event_link'            => $this->getAttributeValue(self::ATTRIBUTE_EVENT_LINK, $attributes),
                    'categories'            => $categories,
                    'occasions'             => $occasions,
                    'location'              => !is_null($locationId) ? (array) $locationId : null,
                    'organizer'             => '',
                    'organizer_phone'       => $this->getAttributeValue(self::ATTRIBUTE_PHONE_NUMBER, $attributes),
                    'organizer_email'       => $this->getAttributeValue(self::ATTRIBUTE_ORGANIZER_EMAIL, $attributes),
                    'coorganizer'           => $this->getAttributeValue(self::ATTRIBUTE_CO_ORGANIZER, $attributes),
                    'contacts'              => !is_null($contactId) ? (array) $contactId : null,
                    'booking_link'          => $this->getAttributeValue(self::ATTRIBUTE_BOOKING_LINK, $attributes),
                    'booking_phone'         => $this->getAttributeValue(self::ATTRIBUTE_BOOKING_PHONE_NUMBER, $attributes),
                    'age_restriction'       => $this->getAttributeValue(self::ATTRIBUTE_AGE_RESTRICTION, $attributes),
                    'price_information'     => $this->getAttributeValue(self::ATTRIBUTE_PRICE_INFORMATION, $attributes),
                    'price_adult'           => $this->getAttributeValue(self::ATTRIBUTE_PRICE_ADULT, $attributes),
                    'price_children'        => $this->getAttributeValue(self::ATTRIBUTE_PRICE_CHILD, $attributes),
                    'accepted'              => 1
                )
            );

            $creatSuccess = $event->save();
            $eventId = $event->ID;
            if($creatSuccess)
            {
                ++$this->nrOfNewEvents;
                $this->levenshteinTitles['event'][] = array('ID' => $event->ID, 'post_title' => $newPostTitle);
            }

            if (!is_null($event->image)) {
                $event->setFeaturedImageFromUrl($event->image);
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

    /**
     * Formats a GMT date to europe stockholm date
     * @param  string $date The GMT date string
     * @return string       The Europe/Stockholm date string
     */
    public function formatDate($date)
    {
        // Format the date string correctly
        $dateParts = explode("T", $date);
        $timeString = substr($dateParts[1], 0, 5);
        $dateString = $dateParts[0] . ' ' . $timeString;

        // Create UTC date object
        $date = new \DateTime($dateString);

        /**
         * @todo Change to get timezone from wp options
         */
        $timeZone = new \DateTimeZone('Europe/Stockholm');
        $date->setTimezone($timeZone);

        return $date->format('Y-m-d H:i:s');
    }
}
