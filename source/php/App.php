<?php

namespace HbgEventImporter;

class App
{
    public $eventsPostType          = null;
    public $locationsPostType       = null;
    public $contactsPostType        = null;
    public $sponsorsPostType        = null;
    public $packagesPostType        = null;
    public $membershipCardsPostType = null;

    public function __construct()
    {
        global $event_db_version;
        $event_db_version = '1.0';

        //Load third party componets
        /*add_action('plugins_loaded', function () {
            if (!class_exists('acf_field_date_time_picker_plugin')) {
                require_once(HBGEVENTIMPORTER_PATH . 'source/php/Vendor/acf-field-date-time-picker/acf-date_time_picker.php');
            }
        });*/
        add_action('init', function () {
            if (!file_exists(WP_CONTENT_DIR . '/mu-plugins/AcfImportCleaner.php') && !class_exists('\\AcfImportCleaner\\AcfImportCleaner')) {
                require_once HBGEVENTIMPORTER_PATH . 'source/php/Helper/AcfImportCleaner.php';
            }
        });
        //Remove auto empty of trash
        add_action('init', function () {
            remove_action('wp_scheduled_delete', 'wp_scheduled_delete');
        });

        //Activations hooks
        register_activation_hook(plugin_basename(__FILE__), '\HbgEventImporter\App::addCronJob');
        register_deactivation_hook(plugin_basename(__FILE__), '\HbgEventImporter\App::removeCronJob');

        //Json load files
        //Remove filter acfJsonLoadPath if load ACF fields with PHP.
        //add_filter('acf/settings/load_json', array($this, 'acfJsonLoadPath'));
        add_action('acf/init', array($this, 'acfSettings'));
        add_filter('acf/translate_field', array($this, 'acfTranslationFilter'));

        //Admin scripts
        add_action('admin_enqueue_scripts', array($this, 'enqueueStyles'));
        add_action('admin_enqueue_scripts', array($this, 'enqueueScripts'));

        //Admin components
        // TA BORT parsePage
        add_action('admin_menu', array($this, 'createParsePage'));
        add_action('admin_notices', array($this, 'adminNotices'));

        // Register cron action
        add_action('import_events_daily', array($this, 'startImport'));

        //add_action('delete_post', array($this, 'checkItOut'), 10);

        // Redirects
        add_filter('login_redirect', array($this, 'loginRedirect'), 10, 3);
        add_action('admin_init', array($this, 'dashboardRedirect'));

        //Check referer (popup box)
        $referer = (isset($_SERVER['HTTP_REFERER'])) ? $_SERVER['HTTP_REFERER'] : null;
        if ((isset($_GET['lightbox']) && $_GET['lightbox'] == 'true') || strpos($referer, 'lightbox=true') > -1) {
            add_action('admin_enqueue_scripts', array($this, 'enqueuStyleSheets'));
        }

        //Init post types
        $this->eventsPostType = new PostTypes\Events();
        $this->locationsPostType = new PostTypes\Locations();
        $this->contactsPostType = new PostTypes\Contacts();
        $this->sponsorsPostType = new PostTypes\Sponsors();
        $this->packagesPostType = new PostTypes\Packages();
        $this->membershipCardsPostType = new PostTypes\MembershipCards();

        //Init functions
        new Taxonomy\EventCategories();
        new Taxonomy\EventTags();
        new Taxonomy\LocationCategories();

        new Admin\Options();
        new Admin\UI();
        new Admin\FilterRestrictions();

        new Acf\AcfFields();

        new Api\Filter();
        new Api\PostTypes();
        new Api\Taxonomies();
        new Api\Linking();
        new Api\LocationFields();
        new Api\ContactFields();
        new Api\EventFields();
        new Api\SponsorFields();
        new Api\PackageFields();
    }

    /**
     * Redirect user after successful login.
     *
     * @param string $redirect_to URL to redirect to.
     * @param string $request URL the user is coming from.
     * @param object $user Logged user's data.
     * @return string
     */
    public function loginRedirect($redirect_to, $request, $user)
    {
        if (! is_wp_error($user)) {
            if (is_array($user->roles)) {
                $urlAdmin = admin_url('edit.php?post_type=event');
                return $urlAdmin;
            }
        } else {
            return $redirect_to;
        }
    }

    /**
     * Redirect user when entering dashboard.
     */
    public function dashboardRedirect()
    {
        global $pagenow;
        if ($pagenow == 'index.php') {
            wp_redirect(admin_url('edit.php?post_type=event'), 301);
            exit;
        }
    }

    public function checkItOut()
    {
        debug_print_backtrace();
        die();
    }

    public function enqueuStyleSheets()
    {
        wp_register_style('lightbox', plugins_url() . '/api-event-manager/dist/css/lightbox.min.css', false, '1.0.0');
        wp_enqueue_style('lightbox');
    }

    public function adminNotices()
    {
        global $current_screen;

        if ($current_screen->id != 'edit-event') {
            return;
        }

        if (isset($_GET['msg']) && $_GET['msg'] == 'import-complete') {
            echo '<div class="updated"><p>Events imported</p></div>';
        }
    }

    /**
     * Enqueue required style
     * @return void
     */
    public function enqueueStyles()
    {
        global $current_screen;
        $type = $current_screen->post_type;
        if ($type == 'event' || $type == 'location' || $type == 'contact' || $type == 'sponsor' || $type == 'package' || $type == 'membership-card') {
            wp_enqueue_style('hbg-event-importer', HBGEVENTIMPORTER_URL . '/dist/css/hbg-event-importer.min.css');
        }
    }

    /**
     * Enqueue required scripts
     * @return void
     */
    public function enqueueScripts()
    {
        global $current_screen;
        $type = $current_screen->post_type;
        if ($type == 'event' || $type == 'location' || $type == 'contact' || $type == 'sponsor' || $type == 'package' || $type == 'membership-card') {
            wp_enqueue_script('hbg-event-importer', HBGEVENTIMPORTER_URL . '/dist/js/hbg-event-importer.min.js');
        }

        wp_localize_script('hbg-event-importer', 'eventmanager', array(
            'require_title'     => __("Title is missing", 'event-manager'),
            'new_contact'       => __("Create new contact", 'event-manager'),
            'new_sponsor'       => __("Create new sponsor", 'event-manager'),
            'new_location'      => __("Create new location", 'event-manager'),
            'new_card'          => __("Create new membership card", 'event-manager'),
            'close'             => __("Close", 'event-manager'),
            'add_images'        => __("Add images", 'event-manager'),
            'similar_posts'     => __("Similar posts", 'event-manager'),
            'new_data_imported' => __("New data imported or updated", 'event-manager'),
            'events'            => __("Events", 'event-manager'),
            'locations'         => __("Locations", 'event-manager'),
            'contacts'          => __("Contacts", 'event-manager'),
            'time_until_reload' => __("Time until reload", 'event-manager'),
            'loading'           => __("Loading", 'event-manager'),
            'choose_time'       => __("Choose time", 'event-manager'),
            'time'              => __("Time", 'event-manager'),
            'hour'              => __("Hour", 'event-manager'),
            'minute'            => __("Minute", 'event-manager'),
            'done'              => __("Done", 'event-manager'),
            'now'               => __("Now", 'event-manager'),
        ));
    }

    /**
     * TA BORT
     * Creates a admin page to trigger update data function
     * ARE NOT USED ANYMORE
     * @return void
     */
    public function createParsePage()
    {
        add_submenu_page(
            null,
            __('Import events', 'hbg-event-importer'),
            __('Import events', 'hbg-event-importer'),
            'edit_posts',
            'import-events',
            function () {
                new \HbgEventImporter\Parser\Xcap('http://mittkulturkort.se/calendar/listEvents.action?month=&date=&categoryPermaLink=&q=&p=&feedType=ICAL_XML');
            }
        );

        add_submenu_page(
            null,
            __('Import CBIS events', 'hbg-event-importer'),
            __('Import CBIS events', 'hbg-event-importer'),
            'edit_posts',
            'import-cbis-events',
            function () {
                new \HbgEventImporter\Parser\CBIS('http://api.cbis.citybreak.com/Products.asmx?wsdl');
            });
// TA BORT
        add_submenu_page(
            null,
            __('Import CBIS locations', 'hbg-event-importer'),
            __('Import CBIS events', 'hbg-event-importer'),
            'edit_posts',
            'import-cbis-locations',
            function () {
                new \HbgEventImporter\Parser\CbisLocation('http://api.info.citybreak.com/Products.asmx?WSDL');
            });
// TA BORT
        add_submenu_page(
            null,
            __('Delete all events', 'hbg-event-importer'),
            __('Delete all events', 'hbg-event-importer'),
            'edit_posts',
            'delete-all-events',
            function () {
                global $wpdb;
                $delete = $wpdb->query("TRUNCATE TABLE `cbis_data`");
                $delete = $wpdb->query("TRUNCATE TABLE `event_occasions`");
                $delete = $wpdb->query("TRUNCATE TABLE `event_postmeta`");
                $delete = $wpdb->query("TRUNCATE TABLE `event_posts`");
                $delete = $wpdb->query("TRUNCATE TABLE `event_stream`");
                $delete = $wpdb->query("TRUNCATE TABLE `event_stream_meta`");
                $delete = $wpdb->query("TRUNCATE TABLE `event_term_relationships`");
                $delete = $wpdb->query("TRUNCATE TABLE `event_term_taxonomy`");
                $delete = $wpdb->query("TRUNCATE TABLE `event_termmeta`");
                $delete = $wpdb->query("TRUNCATE TABLE `event_terms`");
            });
    }

    /**
     *
     * Starts the data import
     * @return void
     */
    public function startImport()
    {
        if (get_field('xcap_daily_cron', 'option') == true) {
            $xcapUrl = 'http://mittkulturkort.se/calendar/listEvents.action' .
                       '?month=&date=&categoryPermaLink=&q=&p=&feedType=ICAL_XML';
            new \HbgEventImporter\Parser\Xcap($xcapUrl);
            // TA BORT
            //file_put_contents(dirname(__FILE__)."/Log/xcap_cron_events.log", "XCAP, Last run: ".date("Y-m-d H:i:s"));
        }
        if (get_field('cbis_daily_cron', 'option') == true) {
            $cbisUrl = 'http://api.cbis.citybreak.com/Products.asmx?wsdl';
            new \HbgEventImporter\Parser\CBIS($cbisUrl);
            new \HbgEventImporter\Parser\CbisLocation($cbisUrl);
            // TA BORT
            //file_put_contents(dirname(__FILE__)."/Log/cbis_cron.log", "CBIS, Last run: ".date("Y-m-d H:i:s"));
        }
    }

    public static function addCronJob()
    {
        wp_schedule_event(time(), 'hourly', 'import_events_daily');
    }

    public static function removeCronJob()
    {
        wp_clear_scheduled_hook('import_events_daily');
    }

    public function acfJsonLoadPath($paths)
    {
        $paths[] = HBGEVENTIMPORTER_PATH . '/acf-exports';
        return $paths;
    }

    /**
     * Creates necessary database table on plugin activation
     */
    public static function database_creation()
    {
        global $wpdb;
        global $event_db_version;
        $table_name = $wpdb->prefix . 'occasions';
        $charset_collate = $wpdb->get_charset_collate();
        $sql = "CREATE TABLE $table_name (
        ID BIGINT(20) UNSIGNED NOT NULL AUTO_INCREMENT,
        event BIGINT(20) UNSIGNED NOT NULL,
        timestamp_start BIGINT(20) UNSIGNED NOT NULL,
        timestamp_end BIGINT(20) UNSIGNED NOT NULL,
        timestamp_door BIGINT(20) UNSIGNED DEFAULT NULL,
        PRIMARY KEY  (ID)
        ) $charset_collate;";
        require_once(ABSPATH . 'wp-admin/includes/upgrade.php');
        dbDelta($sql);
        add_option('event_db_version', $event_db_version);
    }

    /**
     * ACF settings action
     */
    public function acfSettings()
    {
        acf_update_setting('l10n', true);
        acf_update_setting('l10n_textdomain', 'event-manager');
        acf_update_setting('google_api_key', get_option('options_google_geocode_api_key'));
    }

    /**
     * ACF filter to translate specific fields when exporting to PHP
     * @param  array fields to be translated
     * @return array updated fields list
     */
    public function acfTranslationFilter($field)
    {
        if ($field['type'] == 'text' || $field['type'] == 'number') {
            $field['append'] = acf_translate($field['append']);
            $field['placeholder'] = acf_translate($field['placeholder']);
        }

        if ($field['type'] == 'textarea') {
            $field['placeholder'] = acf_translate($field['placeholder']);
        }

        if ($field['type'] == 'repeater') {
            $field['button_label'] = acf_translate($field['button_label']);
        }
        return $field;
    }
}
