<?php

namespace HbgEventImporter;

class App
{
    public $eventPostType       = null;
    public $locationsPostType   = null;
    public $contactsPostType    = null;

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
        add_filter('acf/settings/load_json', array($this, 'acfJsonLoadPath'));

        //Admin scripts
        add_action('admin_enqueue_scripts', array($this, 'enqueueStyles'));
        add_action('admin_enqueue_scripts', array($this, 'enqueueScripts'));

        //Admin components
        //add_action('admin_menu', array($this, 'createParsePage'));
        add_action('admin_notices', array($this, 'adminNotices'));

        // Register cron action
        add_action('import_events_daily', array($this, 'startImport'));

        //add_action('delete_post', array($this, 'checkItOut'), 10);

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

        //Init functions
        new Taxonomy\EventCategories();
        new Taxonomy\EventTags();
        new Taxonomy\LocationCategories();

        new Admin\Options();
        new Admin\UI();
        new Admin\FilterRestrictions();

        new Api\Filter();
        new Api\PostTypes();
        new Api\Linking();
        new Api\LocationFields();
        new Api\ContactFields();
        new Api\EventFields();
    }

    public function checkItOut()
    {
        debug_print_backtrace();
        die();
    }

    public function enqueuStyleSheets()
    {
        wp_register_style('lightbox', plugins_url(
            ) . '/import-event/dist/css/lightbox.dev.css', false, '1.0.0');
        //if ((isset($_GET['lightbox']) && $_GET['lightbox'] == 'true') || strpos($referer, 'lightbox=true') > -1) {
            //wp_register_style('lightbox', plugins_url('', __FILE__) . '/dist/css/lightbox.' . \Modularity\App::$assetSuffix . '.css', false, '1.0.0');

        //}
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

        if ($current_screen->id == 'event' && $current_screen->action == '') {
            wp_enqueue_style('hbg-event-importer', HBGEVENTIMPORTER_URL . '/dist/css/hbg-event-importer.min.css');
        }

        if ($current_screen->id == 'contact' && $current_screen->action == 'add') {
            wp_enqueue_style('hbg-event-importer', HBGEVENTIMPORTER_URL . '/dist/css/hbg-event-importer.min.css');
        }

        wp_enqueue_style('hbg-event-importer', HBGEVENTIMPORTER_URL . '/source/sass/custom.css');
    }

    /**
     * Enqueue required scripts
     * @return void
     */
    public function enqueueScripts()
    {
        global $current_screen;

        if ($current_screen->id == 'event' && $current_screen->action == '') {
            wp_enqueue_script('hbg-event-importer', HBGEVENTIMPORTER_URL . '/dist/js/hbg-event-importer.min.js');
        }

        if ($current_screen->id == 'contact' && $current_screen->action == 'add') {
            wp_enqueue_script('hbg-event-importer', HBGEVENTIMPORTER_URL . '/dist/js/hbg-event-importer.min.js');
        }

        wp_enqueue_script('hbg-event-importer', HBGEVENTIMPORTER_URL . '/source/js/custom.js');
    }

    /**
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
    }

    /**
     * Starts the data import
     * @return void
     */
    public function startImport()
    {
        if (get_field('xcap_daily_cron', 'option') === true || empty(get_field('xcap_daily_cron', 'option'))) {
            $xcapUrl = 'http://mittkulturkort.se/calendar/listEvents.action' .
                       '?month=&date=&categoryPermaLink=&q=&p=&feedType=ICAL_XML';
            new \HbgEventImporter\Parser\Xcap($xcapUrl);
        }
    }

    public function acfJsonLoadPath($paths)
    {
        $paths[] = HBGEVENTIMPORTER_PATH . '/acf-exports';
        return $paths;
    }

    public static function addCronJob()
    {
        wp_schedule_event(time(), 'daily', 'import_events_daily');
    }

    public static function removeCronJob()
    {
        wp_clear_scheduled_hook('import_events_daily');
    }

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

}
