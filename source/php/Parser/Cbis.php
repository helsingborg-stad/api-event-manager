<?php

namespace HbgEventImporter\Parser;

abstract class Cbis extends \HbgEventImporter\Parser
{
    /**
     * API URL for Cbis: http://api.cbis.citybreak.com/
     */

    /**
     * Holds the Soap client
     * @var SoapClient
     */
    private $client = null;

    /**
     * CBIS attribute id's we use
     */
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
    const ATTRIBUTE_ORGANIZER                   =   261;

    /**
     * CBIS attributes we need to decide if we want to use
     */
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

    /**
     * CBIS attribute id's we chose not to use
     */
    const ATTRIBUTE_DIRECTIONS                  =   103;
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
     * Starts the parsing (overrided in extended classes)
     * @return void
     */
    public function start()
    {
        // Override in extending classes
    }

    /**
     * Make a soap request and get the results back
     * @param  string    $key        Cbis api key
     * @param  int|array $geonode    Geonode(s) (id)
     * @param  int       $category   Category id
     * @param  array     $params     Other custom request parameters
     * @return object                SOAP request result object
     */
    public function soapRequest($key, $geonode, $category = null, $params = array())
    {
        if (!isset($key) || empty($key) || !isset($geonode) || empty($geonode)) {
            throw new \Exception('Needed authorization information (CBIS API id and/or CBIS API key) is missing.');
        }

        $this->client = new \SoapClient($this->url, array('keep_alive' => false));

        $requestParams = array(
            'apiKey' => $key,
            'languageId' => 1,
            'categoryId' => $category,
            'templateId' => 0,
            'pageOffset' => 0,
            'itemsPerPage' => 2000,
            'filter' => array(
                'GeoNodeIds' => array($geonode),
                'StartDate' => date('c', strtotime("midnight")),
                'Highlights' => 0,
                'OrderBy' => 'Date',
                'SortOrder' => 'Descending',
                'MaxLatitude' => null,
                'MinLatitude' => null,
                'MaxLongitude' => null,
                'MinLongitude' => null,
                'SubCategoryId' => 0,
                'ProductType' => 'Product',
                'WithOccasionsOnly' => true,
                'ExcludeProductsWithoutOccasions' => true,
                'ExcludeProductsNotInCurrentLanguage' => false,
                'IncludeArchivedProducts' => false,
                'IncludeInactiveProducts' => false,
                'BookableProductsFirst' => false,
                'RandomSortSeed' => 0,
                'ExcludeProductsWhereNameNotInCurrentLanguage' => false,
                'IncludePendingPublish' => false,
            )
        );

        $requestParams = array_replace_recursive($requestParams, $params);

        return $this->client->ListAll($requestParams);
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
     * Get attribute value from attribute id
     * @param  integer $attributeId Attribute id
     * @param  array   $attributes  Attribute haystack
     * @param  mixed   $default     Default return value (if nothing is found)
     * @return mixed                Found attribute value else default value or null
     */
    public function getAttributeValue($attributeId, $attributes, $default = null)
    {
        if (!isset($attributes[$attributeId]) || !isset($attributes[$attributeId]->Data)) {
            return $default;
        }

        return $attributes[$attributeId]->Data;
    }
}
