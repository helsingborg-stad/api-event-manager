<?php

namespace HbgEventImporter\Parser;

require_once("CBISAttributes.php");

class CBIS extends \HbgEventImporter\Parser
{
    public function __construct($url)
    {
        $this->writeAttributeDataToFile = false;
        $this->insertIntoDatabase = false;
        $this->sortedAttributesFromCBIS = array();
        $this->client = new \SoapClient($url, array('keep_alive' => false));
        $this->CBISCONSTANTS = new \CBISAttributes();
        $this->counter = 0;
        $this->countryCodes = $this->setupCountryCodes();
        parent::__construct($url);
    }

    public function start()
    {
        global $wpdb;

        $addArenas = false;
        $addProducts = true;
        $getAllEventsFromCBIS = false;

        $nrProductsToGet = 1;
        $type = 'All';

        if($addArenas)
        {
            //Change to over 120 to get all
            $nrProductsToGet = 150;
            $type = 'Arena';
        }
        else if($addProducts)
        {
            //Change to over 1400 to get all
            $nrProductsToGet = 1500;
            $type = 'Product';
        }

        /*
        How to get sub_fields from a repeater

        if(have_rows('bookingLinks', 238))
        {
            while(have_rows('bookingLinks', 238)) : the_row();
                $link = get_sub_field('link');
                var_dump($link);
            endwhile;
        }
        else
            echo "There are no rows!";

        die();*/

        /**
         * Get API keys from options table
         */
        $cbis_api_key     = "EKRODIUNR2JUSRTQ5F4F4R3NNQKZ3C76";//get_option('helsingborg_cbis_api_key');
        $cbis_hbg_id      = "65072";//get_option('helsingborg_cbis_hbg_id');
        $cbis_category_id = "14086";//get_option('helsingborg_cbis_category_id');

        /**
         * If no keys were found, do not continue
         */
        if (!isset($cbis_api_key) || !isset($cbis_hbg_id) || !isset($cbis_category_id)) {
            return;
        }

        /**
         * Request paramters
         * TODO: Go through params, are these correct?!
         */
        $requestParams = array(
            'apiKey' => $cbis_api_key,
            'languageId' => 1,
            'categoryId' => $cbis_category_id,
            'templateId' => 0,
            'pageOffset' => 0,
            'itemsPerPage' => $nrProductsToGet,
            'filter' => array(
                'GeoNodeIds' => array($cbis_hbg_id),
                'StartDate' => date('c'),
                'Highlights' => 0,
                'OrderBy' => 'Date',
                'SortOrder' => 'Descending',
                'MaxLatitude' => null,
                'MinLatitude' => null,
                'MaxLongitude' => null,
                'MinLongitude' => null,
                'SubCategoryId' => 0,
                'ProductType' => $type,
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

        $attributeParams = array(
            'apiKey'        =>  $cbis_api_key,
            'language'    =>  1
        );

        /**
         * Step 1: Request data with all id's from CBIS SOAP API
         */

        if($addArenas)
        {
            $this->addArenas($this->client->ListAll($requestParams)->ListAllResult->Items->Product);
        }
        else if($addProducts)
        {
            $this->addProducts($this->client->ListAll($requestParams)->ListAllResult->Items->Product);
        }
        else if($getAllEventsFromCBIS)
        {
            $this->getAllEvents($cbis_api_key);
        }

        die();

        //ListAll returns ~1300 products
        /*$response = $client->ListAll($requestParams);

        $totalArenas = $response->ListAllResult->Items->Product;
        echo "Total nr of products :" . count($totalArenas) . "\n";*/
        //var_dump($response);
        //die();


        /*$attributesFromCBIS = $client->GetAttributes($attributeParams)->GetAttributesResult->Attribute;


        foreach($attributesFromCBIS as $namedAttribute) {
            $this->sortedAttributesFromCBIS[$namedAttribute->Id] = $namedAttribute;
        }*/

        //Used if we want to insert and write attributes to file, filepath are hardcoded change to local file
        $attributeFilepath = "/Users/tommymorberg/Desktop/test.csv";

        //Id that does not work for getting product '336512'


        /**
         * Step 2: Iterate and collect products by id from CBIS SOAP API
         * Number of id's per iteration should not exceed 3k but are set to 1k as default
         */

    }

    private function addProducts($idProducts)
    {
        foreach($idProducts as $productKey => $p) {
            ++$this->counter;
            echo "\nThis is product nr: " . $this->counter . "\n";
            //var_dump($p);
            //++$IJustWantToCount;
            //echo "Product nr " . $IJustWantToCount . " Product type: " . $p->ProductType . "!\n";
            //Get all occassions from a product array with array(string startDate, string endDate)
            $occasions = $p->Occasions;
            if(isset($p->Occasions->OccasionObject) && count($p->Occasions->OccasionObject) > 0) {
                $occasions = $p->Occasions->OccasionObject;
            }

            if(!is_array($occasions)) {
                $occasions = array($occasions);
            }

            $occasionsToRegister = array();
            foreach($occasions as $occasion) {
                $startDate = null;
                $endDate = null;
                if(isset($occasion->StartDate)) {
                    $startDate = $this->formatDate($occasion->StartDate);
                }
                if(isset($occasion->EndDate)) {
                    $endDate = $this->formatDate($occasion->EndDate);
                }
                $occasionsToRegister[] = array('startDate' => $startDate, 'endDate' => $endDate);
            }

            /*if($p->Status != null)
                echo "\nStatus is: " . $p->Status . "\n";
            else
            {
                echo "\nThere no status set in this product: \n";
                var_dump($p);
                echo "\nEnd of product without status\n";
            }*/

            if($this->insertIntoDatabase) {
                /*$occ = $p->Occasions;
                if(isset($p->Occasions->OccasionObject) && count($p->Occasions->OccasionObject) > 0)
                    $occ = $p->Occasions->OccasionObject;

                if(!is_array($occ))
                    $occ = array($occ);*/

                $insertValue = $wpdb->insert(
                    'cbis_data',
                    array(
                        'product_id'        =>  $p->Id,
                        'type'              =>  $p->ProductType,
                        'name'              =>  $p->Name,
                        'nr_occasions'      =>  count($occasions),
                        'status'            =>  $p->Status,
                        'published_date'    =>  $p->PublishedDate
                    ),
                    array(
                        '%d',
                        '%s',
                        '%s',
                        '%d',
                        '%s',
                        '%s'
                    )
                );
            }

            $attributes = array();
            $categories = array();

            /*
            SOLVE PROBLEM WITH COLLECTING ALL THE ATTRIBUTES
             */

            //Get all attributes from a product
            foreach($p->Attributes->AttributeData as $attribute) {
                $attributes[$attribute->AttributeId] = $attribute->Value;

                //Example for checking one specific attribute
                /*if($attribute->AttributeId == $this->CBISCONSTANTS::ATTRIBUTE_POSTCODE)
                {
                    if(!$attrCheck)
                    {
                        var_dump($p);
                        $attrCheck = true;
                        echo "ATTRIBUTE_POSTCODE: This is a attribute with the id " . $this->CBISCONSTANTS::ATTRIBUTE_POSTCODE . "\n";
                        var_dump($attribute);
                        echo ("END OF ATTRIBUTE_POSTCODE!\n");
                        die();
                    }
                }*/

                //Collect attributes used and how many times
                /*if(!isset($attributeCheck[$attribute->AttributeId])) {
                    $arrayOfAttributeData = array('Name' => $this->sortedAttributesFromCBIS[$attribute->AttributeId]->Name, 'ID' => $attribute->AttributeId, 'TimesUsed' => 1);
                    $attributeCheck[$attribute->AttributeId] = $arrayOfAttributeData;
                }
                else
                    $attributeCheck[$attribute->AttributeId]['TimesUsed']++;*/
            }

            //Get all categories from a product array with strings
            if($p->Categories != null) {
                if(is_array($p->Categories->Category)) {
                    foreach($p->Categories->Category as $category) {
                        $categories[] = $category->Name;
                    }
                }
                else {
                    $categories[] = $p->Categories->Category->Name;
                }
            }

            $address = $this->getValueByAttributeId($attributes, $this->CBISCONSTANTS::ATTRIBUTE_ADDRESS);
            $ageRestriction = $this->getValueByAttributeId($attributes, $this->CBISCONSTANTS::ATTRIBUTE_AGE_RESTRICTION);
            $alternateName = isset($p->SystemName) && !empty($p->SystemName) ? $p->SystemName : null;
            $bookingLink = $this->getValueByAttributeId($attributes, $this->CBISCONSTANTS::ATTRIBUTE_BOOKING_LINK);
            $bookingLink = $bookingLink != null ? $bookingLink : 'http://www.google.com';
            $bookingPhoneNumber = $this->getValueByAttributeId($attributes, $this->CBISCONSTANTS::ATTRIBUTE_BOOKING_PHONE_NUMBER);
            $city = $p->GeoNode->Name;
            $contactEmail = $this->getValueByAttributeId($attributes, $this->CBISCONSTANTS::ATTRIBUTE_CONTACT_EMAIL);
            $contactEmail = $contactEmail != null ? $contactEmail : 'a@b.se';
            $contactPerson = $this->getValueByAttributeId($attributes, $this->CBISCONSTANTS::ATTRIBUTE_CONTACT_PERSON);
            $contactPerson = $contactPerson != null ? $contactPerson : "Name namesson";
            $contactPhoneNumber = "040-000000";
            $coOrganizer = $this->getValueByAttributeId($attributes, $this->CBISCONSTANTS::ATTRIBUTE_CO_ORGANIZER);
            $country = $this->getValueByAttributeId($attributes, $this->CBISCONSTANTS::ATTRIBUTE_COUNTRY);
            $countryCode = $this->getValueByAttributeId($attributes, $this->CBISCONSTANTS::ATTRIBUTE_COUNTRY_CODE);
            $countryCode2 = $this->getValueByAttributeId($attributes, $this->CBISCONSTANTS::ATTRIBUTE_COUNTRY_CODE2);
            $description = $this->getValueByAttributeId($attributes, $this->CBISCONSTANTS::ATTRIBUTE_DESCRIPTION);
            $description = $description != null ? $description : "";
            $doorTime = null;
            $duration = "";
            $eventlink = $this->getValueByAttributeId($attributes, $this->CBISCONSTANTS::ATTRIBUTE_EVENT_LINK);
            $externalLinks = $this->getValueByAttributeId($attributes, $this->CBISCONSTANTS::ATTRIBUTE_EXTERNAL_LINKS);
            $image = (isset($p->image->Url) ? $product->image->Url : null);
            $ingress = $this->getValueByAttributeId($attributes, $this->CBISCONSTANTS::ATTRIBUTE_INGRESS);
            $latitude = $this->getValueByAttributeId($attributes, $this->CBISCONSTANTS::ATTRIBUTE_LATITUDE);
            $locationName = $address != null ? $address : $city;
            $locationDescription = "";
            $longitude = $this->getValueByAttributeId($attributes, $this->CBISCONSTANTS::ATTRIBUTE_LONGITUDE);
            $municipality = $this->getValueByAttributeId($attributes, $this->CBISCONSTANTS::ATTRIBUTE_MUNICIPALITY);
            $name = $this->getValueByAttributeId($attributes, $this->CBISCONSTANTS::ATTRIBUTE_NAME);
            $name = $name == null ? ($p->Name != null ? $p->Name : null) : $name;
            $organizerEmail = $this->getValueByAttributeId($attributes, $this->CBISCONSTANTS::ATTRIBUTE_ORGANIZER_EMAIL);
            $phoneNumber = $this->getValueByAttributeId($attributes, $this->CBISCONSTANTS::ATTRIBUTE_PHONE_NUMBER);
            $postcode = $this->getValueByAttributeId($attributes, $this->CBISCONSTANTS::ATTRIBUTE_POSTCODE);
            $priceAdult = $this->getValueByAttributeId($attributes, $this->CBISCONSTANTS::ATTRIBUTE_PRICE_ADULT);
            $priceChild = $this->getValueByAttributeId($attributes, $this->CBISCONSTANTS::ATTRIBUTE_PRICE_CHILD);
            $priceInformation = $this->getValueByAttributeId($attributes, $this->CBISCONSTANTS::ATTRIBUTE_PRICE_INFORMATION);
            $publishedDate = isset($p->PublishedDate) && !empty($p->PublishedDate) ? $p->PublishedDate : null;
            $status = isset($p->Status) && !empty($p->Status) ? $p->Status : null;
            $status = $status == 'Active' ? 1 : 0;
            $ticketUrl = "";
            $url = "";
            $uniqueId = $p->Id;
            $website = $this->getValueByAttributeId($attributes, $this->CBISCONSTANTS::ATTRIBUTE_WEB_SITE);

            //We are already getting image through the product object
            //$media = $this->getValueByAttributeId($attributes, $this->CBISCONSTANTS::ATTRIBUTE_MEDIA) == null ? (isset($product->image->Url) ? $product->image->Url : null) : null;

            $data = array(
                //Data for location post
                'locationName'          =>  $locationName,
                'locationDescription'   =>  $locationDescription,
                'country'               =>  $country,
                'municipality'          =>  $municipality,
                'city'                  =>  $city,
                'address'               =>  $address,
                'postcode'              =>  $postcode,
                'latitude'              =>  $latitude,
                'longitude'             =>  $longitude,

                //Data for contact post
                'contactEmail'          =>  $contactEmail,
                'contactPerson'         =>  $contactPerson,
                'contactPhoneNumber'    =>  $contactPhoneNumber,

                //Data for event post
                'name'                  =>  $name,
                'description'           =>  $description,
                'publishedDate'         =>  $publishedDate != null ? $this->formatDate($publishedDate) : $publishedDate,
                'categories'            =>  $categories,
                'image'                 =>  $image,
                'uniqueId'              =>  $uniqueId,

                //Data for event main tab
                'ingress'               =>  $ingress,
                'organizerEmail'        =>  $organizerEmail,
                'phoneNumber'           =>  $phoneNumber,
                'coOrganizer'           =>  $coOrganizer,
                'countryCode'           =>  $countryCode,
                'countryCode2'          =>  $countryCode2,
                'duration'              =>  $duration,
                'doorTime'              =>  $doorTime != null ? $this->formatDate($doorTime) : $doorTime,
                'eventlink'             =>  $eventlink,
                'externalLinks'         =>  $externalLinks,

                //Data for booking tab
                'ticketUrl'             =>  $ticketUrl,
                'bookingLink'           =>  $bookingLink,
                'bookingPhoneNumber'    =>  $bookingPhoneNumber,
                'priceInformation'      =>  $priceInformation,
                'ageRestriction'        =>  $ageRestriction,
                'priceAdult'            =>  $priceAdult,
                'priceChild'            =>  $priceChild,

                //Data for other information tab
                'status'                =>  $status,
                'alternateName'         =>  $alternateName,
                'url'                   =>  $url,
                'website'               =>  $website,

                //Data special
                'occasions'             =>  $occasionsToRegister
            );

            $data = $this->washDataFromCBIS($data);

            //var_dump($data);

            //\HbgEventImporter\Event::add($eventData);
            //Remove when all should be inserted
            //die();
        }
    }

    private function addArenas($arenas)
    {
        foreach($arenas as $arena) {
            ++$this->counter;
            echo "Arena nr: " . $this->counter . "\n";
            var_dump($arena);
            $attributes = array();
            $attributeObject = $arena->Attributes->AttributeData;

            if(!is_array($attributeObject))
            {
                $attributeObject = array($attributeObject);
            }
            foreach($attributeObject as $attribute) {
                $attributes[$attribute->AttributeId] = $attribute->Value;
            }

            $name = $arena->Name;
            $description = $this->getValueByAttributeId($attributes, $this->CBISCONSTANTS::ATTRIBUTE_DESCRIPTION);
            $country = $this->getValueByAttributeId($attributes, $this->CBISCONSTANTS::ATTRIBUTE_COUNTRY);
            $country = $country != null ? $country : "swe";
            $municipality = $this->getValueByAttributeId($attributes, $this->CBISCONSTANTS::ATTRIBUTE_MUNICIPALITY);//Never set in arenas so will be null
            $city = $this->getValueByAttributeId($attributes, $this->CBISCONSTANTS::ATTRIBUTE_POSTAL_ADDRESS);//121
            //$city = $city != null ? $city : "City";
            $address = $this->getValueByAttributeId($attributes, $this->CBISCONSTANTS::ATTRIBUTE_ADDRESS);//117
            $postcode = $this->getValueByAttributeId($attributes, $this->CBISCONSTANTS::ATTRIBUTE_POSTCODE);
            $postcode = $postcode != null ? str_replace(' ', '', $postcode) : '00000';
            $latitude = $this->getValueByAttributeId($attributes, $this->CBISCONSTANTS::ATTRIBUTE_LATITUDE);//113
            $longitude = $this->getValueByAttributeId($attributes, $this->CBISCONSTANTS::ATTRIBUTE_LONGITUDE);//114

            $data = array(
                    'locationName'          =>  $name,
                    'locationDescription'   =>  $description,
                    'country'               =>  $country,
                    'municipality'          =>  $municipality,
                    'city'                  =>  $city,
                    'address'               =>  $address,
                    'postcode'              =>  $postcode,
                    'latitude'              =>  $latitude,
                    'longitude'             =>  $longitude
            );

            //var_dump($locationData);
            //\HbgEventImporter\Location::add($data);

            //Remove when all should be inserted
            //die();
        }
    }

    private function getAllEvents($apiKey)
    {
        //var_dump($this->writeAttributeDataToFile);
        //die();
        //Collect all product id via ListProductIdsLanguageInvariant this lists ~7100 ids for products

        $listOfAllIds = $this->client->ListProductIdsLanguageInvariant(array('apiKey' =>  $apiKey))->ListProductIdsLanguageInvariantResult->ProductMapItem;

        $totalCountOfIds = count($listOfAllIds);
        echo "Total nr of id's = " . $totalCountOfIds . "\n";

        $reIterate = true;
        $iterateIncrease = 500;
        $loopCount = 0;
        $totalIterations = 0;
        $start = 0;
        $attributeCheck = array();

        //Only used when debugging
        $attrCheck = false;
        while($reIterate)
        {
            //$reIterate = false;
            $allProductIds = array();
            for($i = $start, $e = $start + $iterateIncrease; $i < $e; ++$i) {
                $allProductIds[] = $listOfAllIds[$i]->ProductId;
                ++$totalIterations;
                if($totalIterations >= $totalCountOfIds)
                {
                    echo "The end reached!\n";
                    $reIterate = !$reIterate;
                    break;
                }
            }
            $start += $iterateIncrease;
            ++$loopCount;

            $trueParams = array(
                'apiKey'        =>  $apiKey,
                'languageId'    =>  1,
                'pageOffset'    =>  0,
                'itemsPerPage'  =>  $iterateIncrease,
                'ids'           =>  $allProductIds
            );

            $allProductsHere = $this->client->WithIds($trueParams)->WithIdsResult->Items->Product;
            //var_dump($allProductsHere);
            //die();
            self::addProducts($allProductsHere);
        }

        if($this->writeAttributeDataToFile)
            echo $this->WriteAttributeData($attributeCheck);
    }

    private function washDataFromCBIS(array $cbisData)
    {
        /*$testArray = array();
        $testArray["derp"] = 1;
        $testArray["slurp"] = 2;
        $testArray["hello"] = 3;
        $testArray["world"] = 4;

        var_dump($testArray);
        foreach($testArray as $key => &$value) {
            $value = "nooooo";
        }
        var_dump($testArray);
        die();*/
        foreach($cbisData as $key => &$value) {
            if(is_string($value))
            {
                if($key == "organizerEmail")
                {
                    /*
                    If there are no @ in the email just null it
                     */
                    if(strpos($value, '@') === false)
                        $value = null;
                }

                if($key == "contactPhoneNumber" || $key == "phoneNumber")
                {
                    $value = preg_replace("/[^0-9,.]/", "", $value);
                }

                echo "Key: " . $key . "\n";
                var_dump($value);

                //List of all fields that should be washed
                //Remove all spaces from contacts telephone number $key = "contactPhoneNumber"
                //Remove all spaces from telephone number $key = "phoneNumber"
                //

                /*
                First we strip beginning and trailing spaces
                 */
                /*$occurrences = 0;
                $stringBefore = $value;
                $stringLength = strlen($value);
                $value = ltrim($value);
                $value = rtrim($value);
                if($stringLength != strlen($value))
                {
                    echo "String changed, before: \n";
                    var_dump($stringBefore);
                    echo "String changed, after: \n";
                    var_dump($value);
                }*/
                /*
                Replacing the string ' (copy)' with ''
                 */
                /*$value = str_replace(" (copy)", "", $value, $occurrences);
                if($occurrences > 0)
                {
                    echo "Number of occurrences: " . $occurrences . "\n";
                    echo "Before:\n";
                    var_dump($stringBefore);
                    echo "After:\n";
                    var_dump($value);
                }*/
            }
        }

        return $cbisData;
    }

    public function getValueByAttributeId(&$attributes, $attributeId)
    {
        if(isset($attributes[$attributeId]) && !isset($attributes[$attributeId]->Data))
        {
            echo "Inside getValue\n";
            var_dump($attributes[$attributeId]);
            die();
        }

        return isset($attributes[$attributeId]) ? $attributes[$attributeId]->Data : null;
    }

    public function WriteAttributeData($attributes)
    {
        $returnMessage = "Failed to open file!";
        $fileName = $attributeFilepath;
        if(file_exists($fileName)) {
            $fileHandle = fopen($fileName, "w");
            $returnMessage = "Success! Wrote to file '" . $fileName . "'!";
            foreach($attributes as $attr) {
                $values = array($attr['Name'], $attr['ID'], $attr['TimesUsed']);
                fputcsv($fileHandle, $values);
            }
            fclose($fileHandle);
        }
        return $returnMessage;
    }

    public function formatDate($date)
    {
        // Format the date string correctly
        $dateParts = explode("T", $date);
        $timeString = substr($dateParts[1], 0, 5);
        $dateString = $dateParts[0] . ' ' . $timeString;

        // Create UTC date object
        $date = new \DateTime($dateString);
        $timeZone = new \DateTimeZone('Europe/Stockholm');
        $date->setTimezone($timeZone);

        return $date->format('Y-m-d H:i:s');
    }

    private function setupCountryCodes()
    {
        /*
        Reference at https://sammaye.wordpress.com/2010/11/05/php-countries-and-their-call-codes-with-two-letter-abbreviations/
         */
        $countries = array();
        $countries[] = array("code"=>"SE","name"=>"Sweden","d_code"=>"+46");
        $countries[] = array("code"=>"DK","name"=>"Denmark","d_code"=>"+45");
        $countries[] = array("code"=>"NO","name"=>"Norway","d_code"=>"+47");
        $countries[] = array("code"=>"FI","name"=>"Finland","d_code"=>"+358");
        $countries[] = array("code"=>"IS","name"=>"Iceland","d_code"=>"+354");
        return $countries;
    }
}

