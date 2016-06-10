<?php
/*
* CBIS scheduled event. Loads events from CBIS api and adds to DB
* This should occur each day at 22.30, so new events are added in our
* database as external events. This file is included from functions.php
*/
/* Function to execute as event, from setup above */
add_action( 'scheduled_cbis', 'cbis_event' );
function cbis_event() {
    global $wpdb;
    /**
     * Get API keys from options table
     */
    $cbis_api_key     = get_option('helsingborg_cbis_api_key');
    $cbis_hbg_id      = get_option('helsingborg_cbis_hbg_id');
    $cbis_category_id = get_option('helsingborg_cbis_category_id');
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
        'itemsPerPage' => 1000,
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
            'ProductType' => 'Product',
            'WithOccasionsOnly' => false,
            'ExcludeProductsWithoutOccasions' => false,
            'ExcludeProductsNotInCurrentLanguage' => false,
            'IncludeArchivedProducts' => false,
            'IncludeInactiveProducts' => false,
            'BookableProductsFirst' => false,
            'RandomSortSeed' => 0,
            'ExcludeProductsWhereNameNotInCurrentLanguage' => false,
            'IncludePendingPublish' => true
        )
    );
    /**
     * Step 1: Request data from CBIS SOAP API
     */
    $client = new SoapClient('http://api.cbis.citybreak.com/Products.asmx?WSDL');
    $response = $client->ListAll($requestParams);
    $products = $response->ListAllResult->Items->Product;
    if (!count($products)) {
        return;
    }
    /**
     * Step 2: Delete all previous city break events
     */
    $delete_query = "DELETE FROM happy_external_event WHERE ImageID LIKE '%citybreak%'";
    $result = $wpdb->get_results($delete_query);

    /**
     * Step 3: Loop the loaded events, map the data and save to database
     */
    foreach($products as $product) {
        /**
         * Loop attributes and populate new array where key is AttributeId
         * @var array
         */
        $attributes = array();
        foreach ($product->Attributes->AttributeData as $attribute) {
            $attributes[$attribute->AttributeId] = $attribute->Value;
        }
        /**
         * Map attributes to correct variables
         */
        $title        = isset($product->Name) && !empty($product->Name) ? $product->Name : $product->SystemName;
        $status       = $product->Status       ?: 'Övrigt';
        $imageid      = $product->Image->Url   ?: '';
        $introduction = $attributes[101]->Data ?: '';
        $description  = $attributes[102]->Data ?: '';
        $link         = $attributes[125]->Data ?: '';
        /**
         * Get the event category
         * Always uses the last category found
         */
        foreach ($product->Categories as $category) {
            $type = $category->Name;
        }
        $occations = $product->Occasions;
        if (isset($product->Occasions->OccasionObject) && count($product->Occasions->OccasionObject) > 0) {
            $occations = $product->Occasions->OccasionObject;
        }
        if (!is_array($occations)) {
            $occations = array($occations);
        }
        /**
         * Loop occations
         */
        foreach ($occations as $occasion) {
            // Make sure the occasion has a startdate !
            if (isset($occasion->StartDate)) {
                /**
                 * Map id and location to variables
                 */
                $id       = $occasion->Id;
                $location = $occasion->ArenaName;
                /**
                 * Create proper DateTime obj. from string (yyyy-mm-ddThh:mm:ss)
                 */
                $date = DateTime::CreateFromFormat('Y-m-d\TH:i:s', $occasion->StartDate);
                $time = DateTime::CreateFromFormat('Y-m-d\TH:i:s', $occasion->StartTime);
                /**
                 * Save the event to the database
                 */
                $wpdb->insert('happy_external_event',
                    array(
                        'ID'          => $id,
                        'Name'        => $title,
                        'Status'      => $status,
                        'Description' => $description,
                        'EventType'   => $type,
                        'Date'        => $date->format('Y-m-d'),
                        'Time'        => $time->format('H:i'),
                        'Location'    => $location,
                        'ImageID'     => $imageid,
                        'Link'        => $link
                    ),
                    array(
                        '%d',
                        '%s',
                        '%s',
                        '%s',
                        '%s',
                        '%s',
                        '%s',
                        '%s',
                        '%s',
                        '%s'
                    )
                );
            }
        }
    }
    /**
     * Start stored procedure
     * @var mysqli
     */
    $mysqli    = new mysqli(DB_HOST, DB_USER, DB_PASSWORD, DB_NAME);
    $procedure = "CALL spInsertIntoHappyEvent();";
    $mysqli->real_query($procedure);
}
