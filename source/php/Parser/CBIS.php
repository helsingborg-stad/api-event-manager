<?php

namespace HbgEventImporter\Parser;

use \HbgEventImporter\Event as Event;
use \HbgEventImporter\Location as Location;
use \HbgEventImporter\Contact as Contact;

class Cbis extends \HbgEventImporter\Parser
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
    private $productType = 'Product';

    /**
     * Holds a list of all found events
     * @var array
     */
    private $events = array();

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
        $this->client = new \SoapClient($this->url, array('keep_alive' => false));

        $cbisKey = 'EKRODIUNR2JUSRTQ5F4F4R3NNQKZ3C76';
        $cbisId = 65072;
        $cbisCategory = 14086;

        if (!isset($cbisKey) || empty($cbisKey)) {
            throw new \Exception('No $cbisKey supplied');
        }

        // Number of events to get
        $getLength = 1500;
        if ($this->productType == 'Arena') {
            $getLength = 150;
        }

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
                'WithOccasionsOnly' => false,
                'ExcludeProductsWithoutOccasions' => false,
                'ExcludeProductsNotInCurrentLanguage' => false,
                'IncludeArchivedProducts' => true,
                'IncludeInactiveProducts' => true,
                'BookableProductsFirst' => false,
                'RandomSortSeed' => 0,
                'ExcludeProductsWhereNameNotInCurrentLanguage' => false,
                'IncludePendingPublish' => true
            )
        );

        // Get and save the events
        $this->events = $this->client->ListAll($requestParams)->ListAllResult->Items->Product;

        foreach ($this->events as $eventData) {
            $this->saveEvent($eventData);
        }

        return true;
    }

    /**
     * Get attributes from event data
     * @param  object $eventData Event data object
     * @return array             Attributes
     */
    public function getAttributes($eventData)
    {
        $attributes = array();

        foreach ($eventData->Attributes->AttributeData as $attribute) {
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

        if (!is_null($eventData->Categories)) {
            if (is_array($eventData->Categories->Category)) {
                foreach ($eventData->Categories->Category as $category) {
                    $categories[] = $category->Name;
                }
            } else {
                $categories[] = $eventData->Categories->Category->Name;
            }
        }

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

        $occasions = (array) $occasions;

        foreach ($occasions as $occasion) {
            $startDate = null;
            $endDate = null;
            if (isset($occasion->StartDate)) {
                $startDate = $this->formatDate($occasion->StartDate);
            }
            if (isset($occasion->EndDate)) {
                $endDate = $this->formatDate($occasion->EndDate);
            }

            $occasionsToRegister[] = array('startDate' => $startDate, 'endDate' => $endDate);
        }

        return $occasionsToRegister;
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

        // Create the location
        $location = new Location(
            array(
                'post_title' => $this->getAttributeValue(self::ATTRIBUTE_ADDRESS, $attributes) ? $this->getAttributeValue(self::ATTRIBUTE_ADDRESS, $attributes) : $eventData->GeoNode->Name
            ),
            array(
                'description'           => null,
                'country'               => $this->getAttributeValue(self::ATTRIBUTE_COUNTRY, $attributes),
                'municipality'          => $this->getAttributeValue(self::ATTRIBUTE_MUNICIPALITY, $attributes),
                'city'                  => $eventData->GeoNode->Name,
                'postalAddress'         => $this->getAttributeValue(self::ATTRIBUTE_ADDRESS, $attributes),
                'postcode'              => $this->getAttributeValue(self::ATTRIBUTE_POSTCODE, $attributes),
                'latitude'              => $this->getAttributeValue(self::ATTRIBUTE_LATITUDE, $attributes),
                'longitude'             => $this->getAttributeValue(self::ATTRIBUTE_LONGITUDE, $attributes),
                '_event_manager_uid'    => $this->getAttributeValue(self::ATTRIBUTE_ADDRESS, $attributes) ? $this->getAttributeValue(self::ATTRIBUTE_ADDRESS, $attributes) : $eventData->GeoNode->Name
            )
        );

        $locationId = $location->save();

        // Save contact
        $contact = new Contact(
            array(
                'post_title'            => $this->getAttributeValue(self::ATTRIBUTE_CONTACT_PERSON, $attributes) . ': ' . strtolower($this->getAttributeValue(self::ATTRIBUTE_CONTACT_EMAIL, $attributes))
            ),
            array(
                'name'                  => $this->getAttributeValue(self::ATTRIBUTE_CONTACT_PERSON, $attributes),
                'email'                 => strtolower($this->getAttributeValue(self::ATTRIBUTE_CONTACT_EMAIL, $attributes)),
                'phoneNumber'           => null,
                '_event_manager_uid'    => $this->getAttributeValue(self::ATTRIBUTE_CONTACT_PERSON, $attributes) . ': ' . strtolower($this->getAttributeValue(self::ATTRIBUTE_CONTACT_EMAIL, $attributes))
            )
        );

        $contactId = $contact->save();

        // Creates the event object
        $event = new Event(
            array(
                'post_title'            => $this->getAttributeValue(self::ATTRIBUTE_NAME, $attributes, ($eventData->Name != null ? $eventData->Name : null)),
                'post_content'          => $this->getAttributeValue(self::ATTRIBUTE_INGRESS, $attributes) . "\n\n" . $this->getAttributeValue(self::ATTRIBUTE_DESCRIPTION, $attributes)
            ),
            array(
                'name'                  => $this->getAttributeValue(self::ATTRIBUTE_NAME, $attributes, ($eventData->Name != null ? $eventData->Name : null)),
                'description'           => $this->getAttributeValue(self::ATTRIBUTE_DESCRIPTION, $attributes),
                'publishedDate'         => '', // NEED TO FIX
                'categories'            => $categories,
                'image'                 => (isset($eventData->image->Url) ? $eventData->image->Url : null),
                'uniqueId'              => 'cbis-' . $eventData->Id,

                //Data for event main tab
                'ingress'               => $this->getAttributeValue(self::ATTRIBUTE_INGRESS, $attributes),
                'organizerEmail'        => $this->getAttributeValue(self::ATTRIBUTE_ORGANIZER_EMAIL, $attributes),
                'phoneNumber'           => $this->getAttributeValue(self::ATTRIBUTE_PHONE_NUMBER, $attributes),
                'coOrganizer'           => $this->getAttributeValue(self::ATTRIBUTE_CO_ORGANIZER, $attributes),
                'countryCode'           => $this->getAttributeValue(self::ATTRIBUTE_COUNTRY_CODE, $attributes),
                'countryCode2'          => $this->getAttributeValue(self::ATTRIBUTE_COUNTRY_CODE2, $attributes),
                'duration'              => '',
                'doorTime'              => '',
                'eventlink'             => $this->getAttributeValue(self::ATTRIBUTE_EVENT_LINK, $attributes),
                'externalLink'          => $this->getAttributeValue(self::ATTRIBUTE_EXTERNAL_LINKS, $attributes),

                // Data for locaiton tab
                'location'              => (array) $locationId,

                // Data for contacts tab
                'contacts'              => (array) $contactId,

                //Data for booking tab
                'ticketUrl'             => '',
                'bookingLink'           => $this->getAttributeValue(self::ATTRIBUTE_BOOKING_LINK, $attributes),
                'bookingPhoneNumber'    => $this->getAttributeValue(self::ATTRIBUTE_BOOKING_PHONE_NUMBER, $attributes),
                'priceInformation'      => $this->getAttributeValue(self::ATTRIBUTE_PRICE_INFORMATION, $attributes),
                'ageRestriction'        => $this->getAttributeValue(self::ATTRIBUTE_AGE_RESTRICTION, $attributes),
                'priceAdult'            => $this->getAttributeValue(self::ATTRIBUTE_PRICE_ADULT, $attributes),
                'priceChild'            => $this->getAttributeValue(self::ATTRIBUTE_PRICE_CHILD, $attributes),

                //Data for other information tab
                'status'                => isset($eventData->Status) && !empty($eventData->Status) ? $eventData->Status : null,
                'alternateName'         => isset($eventData->SystemName) && !empty($eventData->SystemName) ? $eventData->SystemName : null,
                'url'                   => '',
                'website'               => $this->getAttributeValue(self::ATTRIBUTE_WEB_SITE, $attributes),

                //Data special
                'occasions'             => $occasions,

                '_event_manager_uid'    => 'cbis-' . $eventData->Id
            )
        );

        $eventId = $event->save();
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
            echo "Inside getValue\n";
            var_dump($attributes[$attributeId]);
            die();
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
