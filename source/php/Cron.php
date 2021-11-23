<?php

namespace HbgEventImporter;

class Cron
{
    public static $postTypeSlug = 'event';
    public static $clients = array('cbis', 'xcap', 'transticket', 'arcgis', 'ols');

    public function __construct()
    {
        add_action('remove_expired_events', array($this, 'expiredEvents'));
        add_action('acf/save_post', array($this, 'scheduleImportCron'), 20);
        add_action('import_events', array($this, 'startImport'));
        add_action('wp_ajax_schedule_single_import', array($this, 'scheduleSingleImport'));
    }

    /**
     * Schedule single import
     */
    public function scheduleSingleImport()
    {
        if (!in_array($client = $_POST['client'] ?? null, self::$clients)) {
            wp_die();
        }
        wp_schedule_single_event(time(), 'import_events', array('client' => $client));
        wp_die();
    }

    /**
     * Schedule import
     * @param  int $postId
     * @return void
     */
    public function scheduleImportCron($postId)
    {
        if ($postId !== 'options' || get_current_screen()->id !== 'event_page_acf-options-options') {
            return;
        }

        //Get active clients
        $activeClients = array();
        foreach (self::$clients as $client) {
            wp_clear_scheduled_hook('import_events', array('client' => $client));
            if (get_field($client . '_daily_cron', 'option') == true) {
                $activeClients[] = $client;
            }
        }

        //Create schedules
        foreach ($activeClients as $clientKey => $client) {
            wp_schedule_event(
                time() + $this->calculateCronOffsetTime($activeClients, 3600, $clientKey),
                'hourly',
                'import_events',
                array('client' => $client)
            );
        }
    }

    /**
     * Calculate a time offset for each integration
     *
     * @param  array   $clients   The client to be scheduled
     * @param  integer $timeFrame Total schedule space
     * @param  integer $iteration Current iteration in loop
     *
     * @return integer
     */
    public function calculateCronOffsetTime($clients, $timeFrame = 3600, $iteration = 0) : int
    {
        if ($numberOfClients = count($clients)) {
            return ($timeFrame/$numberOfClients) * $iteration;
        }
        return 0;
    }

    /**
     * Register remove expired events cron job
     * @return void
     */
    public static function addCronJob()
    {
        wp_schedule_event(strtotime('tomorrow +3 hours'), 'daily', 'remove_expired_events');
    }

    /**
     * Clears cron jobs
     * @return void
     */
    public static function removeCronJob()
    {
        foreach (self::$clients as $client) {
            wp_clear_scheduled_hook('import_events', array('client' => $client));
        }
        wp_clear_scheduled_hook('remove_expired_events');
    }

    /**
     * Cron method for removing expired events
     * @return void
     */
    public function expiredEvents()
    {
        $this->removeExpiredOccasions();
        $this->trashExpiredEvents();
    }

    /**
     * Remove expired occasions from database
     * @return void
     */
    public function removeExpiredOccasions()
    {
        global $wpdb;
        $dateLimit = strtotime('midnight now') - 1;

        // Get all passed occasions from database
        $dbTable = $wpdb->prefix . 'occasions';
        $query = "
            SELECT * FROM $dbTable
            WHERE timestamp_end < {$dateLimit}
            ORDER BY timestamp_start DESC
        ";
        $occasions = $wpdb->get_results($query, ARRAY_A);

        if (!is_array($occasions) || empty($occasions)) {
            return;
        }

        // Delete all passed occasions
        foreach ($occasions as $occasion) {
            $id = $occasion['ID'];
            $wpdb->delete($dbTable, array('ID' => $id));
        }

        return;
    }

    /**
     * Trash expired events
     * @return void
     */
    public function trashExpiredEvents()
    {
        global $wpdb;
        // Get all events from database
        $query = "
            SELECT ID FROM $wpdb->posts
            WHERE post_type = %s
            AND post_status = %s
        ";
        $completeQuery = $wpdb->prepare($query, self::$postTypeSlug, 'publish');
        $events = $wpdb->get_results($completeQuery);

        if (count($events) == 0) {
            return;
        }

        $dbTable = $wpdb->prefix . 'occasions';
        $query = "
            SELECT ID, event FROM $dbTable
            WHERE event = %s
        ";
        // Loop through events and check if any occasions exist
        foreach ($events as $event) {
            $preparedQuery = $wpdb->prepare($query, $event->ID);
            $results = $wpdb->get_results($preparedQuery);
            // Move event to trash if occasions is empty
            if (count($results) == 0) {
                wp_trash_post((int)$event->ID);
            }
        }

        return;
    }

    /**
     * Get Transticket keys
     * @return array
     */
    public function getTransTicketKeys(): array
    {
        $transticketKeys = array();

        if (!have_rows('transticket_api_urls', 'option')) {
            return array();
        }

        while (have_rows('transticket_api_urls', 'option')) {
            the_row();

            $transticketKeys[] = array(
                'transticket_api_url' => get_sub_field('transticket_api_url'),
                'transticket_api_key' => get_sub_field('transticket_username') . ":" . get_sub_field('transticket_password'),
                'transticket_filter_tags' => get_sub_field('transticket_filter_tags'),
                'transticket_groups' => get_sub_field('transticket_publishing_groups'),
                'transticket_ticket_url' => get_sub_field('transticket_ticket_url'),
                'transticket_weeks' => get_sub_field('transticket_weeks'),
                'transticket_group_occasions' => get_sub_field('transticket_group_occasions'),
                'default_city' => get_sub_field('transticket_default_city')
            );
        }

        return $transticketKeys;
    }

    /**
     * Get Xcap keys
     * @return array
     */
    public function getXcapKeys(): array
    {
        $xcapKeys = array();

        if (!have_rows('xcap_api_urls', 'option')) {
            return array();
        }

        while (have_rows('xcap_api_urls', 'option')) {
            the_row();

            $xcapKeys[] = array(
                'xcap_api_url' => get_sub_field('xcap_api_url'),
                'xcap_exclude' => get_sub_field('xcap_filter_categories'),
                'xcap_groups' => get_sub_field('xcap_publishing_groups'),
                'default_city' => get_sub_field('xcap_default_city')
            );
        }

        return $xcapKeys;
    }

    /**
     * Get CBIS keys
     * @return array
     */
    public function getCbisKeys(): array
    {
        $cbisKeys = array();

        if (!have_rows('cbis_api_keys', 'option')) {
            return array();
        }

        while (have_rows('cbis_api_keys', 'option')) {
            the_row();

            // What
            $locationCategories = array();
            $locationCategories[] = array(
                'arena' => 1,
                'cbis_location_cat_id' => get_sub_field('cbis_event_id'),
                'cbis_location_name' => 'arena'
            );

            // What
            if (!empty(get_sub_field('cbis_location_ids'))) {
                foreach (get_sub_field('cbis_location_ids') as $location) {
                    $locationCategories[] = array(
                        'arena' => 0,
                        'cbis_location_cat_id' => $location['cbis_location_cat_id'],
                        'cbis_location_name' => $location['cbis_location_name']
                    );
                }
            }

            $cbisKeys[] = array(
                'cbis_key' => get_sub_field('cbis_api_product_key'),
                'cbis_geonode' => get_sub_field('cbis_api_geonode_id'),
                'cbis_event_id' => get_sub_field('cbis_event_id'),
                'cbis_exclude' => get_sub_field('cbis_filter_categories'),
                'cbis_groups' => get_sub_field('cbis_publishing_groups'),
                'cbis_locations' => $locationCategories,
                'default_city' => get_sub_field('cbis_default_city')
            );
        }

        return $cbisKeys;
    }

    /**
     * Get ArcGIS keys
     * @return array
     */
    public function getArcgisKeys(): array
    {
        $arcgisKeys = array();

        if (!have_rows('arcgis_api_urls', 'option')) {
            return array();
        }

        while (have_rows('arcgis_api_urls', 'option')) {
            the_row();

            $arcgisKeys[] = array(
                'arcgis_api_url' => get_sub_field('arcgis_api_url'),
                'arcgis_groups' => get_sub_field('arcgis_publishing_groups'),
                'default_city' => get_sub_field('arcgis_default_city')
            );
        }

        return $arcgisKeys;
    }

    /**
     * Get OpenLib keys
     * @return array
     */
    public function getOpenLibKeys(): array
    {
        $keys = array();

        if (!have_rows('ols_api_urls', 'option')) {
            return array();
        }

        while (have_rows('ols_api_urls', 'option')) {
            the_row();

            $keys[] = array(
                'api_url' => get_sub_field('ols_api_url'),
                'api_key' => get_sub_field('ols_api_key'),
                'group_id' => !empty(get_sub_field('ols_group_id')) ? get_sub_field('ols_group_id') : null,
                'default_groups' => get_sub_field('ols_publishing_groups'),
            );
        }

        return $keys;
    }

    /**
     * Starts the data import
     * @param $client
     * @return void
     */
    public function startImport($client)
    {
        $transient = 'api-event-manager-importing-' . $client;
        // Bail if import is already running
        if (get_site_transient($transient)) {
            return;
        }

        // Set transient
        set_site_transient($transient, true, 20 * MINUTE_IN_SECONDS);

        switch ($client) {
            case 'cbis':
                $api_keys = $this->getCbisKeys();
                foreach ((array)$api_keys as $key => $api_key) {
                    new \HbgEventImporter\Parser\CbisEvent('http://api.cbis.citybreak.com/Products.asmx?wsdl', $api_key);
                }
                // Cbis locations
                foreach ((array)$api_keys as $key => $api_key) {
                    foreach ($api_key['cbis_locations'] as $key => $location) {
                        new \HbgEventImporter\Parser\CbisLocation('http://api.cbis.citybreak.com/Products.asmx?wsdl', $api_key, $location);
                    }
                }
                break;
            case 'xcap':
                $api_keys = $this->getXcapKeys();
                foreach ((array)$api_keys as $key => $api_key) {
                    new \HbgEventImporter\Parser\Xcap($api_key['xcap_api_url'], $api_key);
                }
                break;
            case 'transticket':
                $api_keys = $this->getTransTicketKeys();
                foreach ((array)$api_keys as $key => $api_key) {
                    new Parser\TransTicket($api_key['transticket_api_url'], $api_key);
                }
                break;
            case 'arcgis':
                $api_keys = $this->getArcgisKeys();
                foreach ((array)$api_keys as $key => $api_key) {
                    new Parser\Arcgis($api_key['arcgis_api_url'], $api_key);
                }
                break;
            case 'ols':
                $api_keys = $this->getOpenLibKeys();
                foreach ((array)$api_keys as $args) {
                    new Parser\OpenLib($args['api_url'], $args);
                }
                break;
        }

        // Delete transient
        delete_site_transient($transient);
    }
}
