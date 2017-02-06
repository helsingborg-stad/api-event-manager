<?php

namespace HbgEventImporter;

class App
{
    public function __construct()
    {
        global $event_db_version;
        $event_db_version = '1.0';

        add_action('init', function () {
            if (!file_exists(WP_CONTENT_DIR . '/mu-plugins/AcfImportCleaner.php') && !class_exists('\\AcfImportCleaner\\AcfImportCleaner')) {
                require_once HBGEVENTIMPORTER_PATH . 'source/php/Helper/AcfImportCleaner.php';
            }
        });

        //Remove auto empty of trash
        add_action('init', function () {
            remove_action('wp_scheduled_delete', 'wp_scheduled_delete');
        });

        //Field mapping
        add_action('acf/init', array($this, 'acfSettings'));
        add_filter('acf/translate_field', array($this, 'acfTranslationFilter'));

        //Admin scripts
        add_action('admin_enqueue_scripts', array($this, 'enqueueStyles'));
        add_action('admin_enqueue_scripts', array($this, 'enqueueScripts'));

        // Set api keys
        add_action('admin_enqueue_scripts', array($this, 'setApiKeys'));

        //Admin components
        // Debug code createParsePage, TA BORT
        add_action('admin_menu', array($this, 'createParsePage'));
        add_action('admin_notices', array($this, 'adminNotices'));

        // Register cron action
        add_action('import_events_daily', array($this, 'startImport'));

        // Redirects
        add_filter('login_redirect', array($this, 'loginRedirect'), 10, 3);
        add_action('admin_init', array($this, 'dashboardRedirect'));

        //Check referer (popup box)
        $referer = (isset($_SERVER['HTTP_REFERER'])) ? $_SERVER['HTTP_REFERER'] : null;
        if ((isset($_GET['lightbox']) && $_GET['lightbox'] == 'true') || strpos($referer, 'lightbox=true') > -1) {
            add_action('admin_enqueue_scripts', array($this, 'enqueuStyleSheets'));
        }

        //Init post types
        new PostTypes\Events();
        new PostTypes\Locations();
        new PostTypes\Contacts();
        new PostTypes\Sponsors();
        new PostTypes\Packages();
        new PostTypes\MembershipCards();
        new PostTypes\Guides();

        //Init functions
        new Acf\AcfFields();

        new Taxonomy\EventCategories();
        new Taxonomy\UserGroups();
        new Taxonomy\EventTags();
        new Taxonomy\LocationCategories();

        new Admin\Options();
        new Admin\UI();
        new Admin\FilterRestrictions();

        new Api\Filter();
        new Api\PostTypes();
        new Api\Taxonomies();
        new Api\Linking();
        new Api\EventFields();
        new Api\LocationFields();
        new Api\ContactFields();
        new Api\SponsorFields();
        new Api\PackageFields();
        new Api\MembershipCards();
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

            if (isset($_GET['page']) && in_array($_GET['page'], array('acf-upgrade'))) {
                return;
            }

            wp_redirect(admin_url('edit.php?post_type=event'), 301);
            exit;
        }
    }

    public function enqueuStyleSheets()
    {
        wp_enqueue_style('lightbox', plugins_url() . '/api-event-manager/dist/css/lightbox.min.css', false, '1.0.0');
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
        if (in_array($current_screen->post_type, array('event', 'location', 'contact', 'sponsor', 'package', 'membership-card'))) {
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

        if ($type == 'event' || $type == 'location' || $type == 'contact' || $type == 'sponsor' || $type == 'package' || $type == 'membership-card' || $current_screen->base == 'admin_page_cron-import') {
            wp_enqueue_script('hbg-event-importer', HBGEVENTIMPORTER_URL . '/dist/js/hbg-event-importer.min.js');
        }

        wp_localize_script('hbg-event-importer', 'eventmanager', array(
            'ajaxurl'           => admin_url('admin-ajax.php'),
            'require_title'     => __("Title is missing", 'event-manager'),
            'new_contact'       => __("Create new contact", 'event-manager'),
            'new_sponsor'       => __("Create new sponsor", 'event-manager'),
            'new_location'      => __("Create new location", 'event-manager'),
            'new_card'          => __("Create new membership card", 'event-manager'),
            'close'             => __("Close", 'event-manager'),
            'add_images'        => __("Add images", 'event-manager'),
            'similar_posts'     => __("Similar posts", 'event-manager'),
            'new_data_imported' => __("Imported data", 'event-manager'),
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
     * Set API keys from options as js variables
     * @return void
     */
    public function setApiKeys()
    {
        if (current_user_can('administrator')) {
            // Set CBIS API variables
            $cbis_keys = array();
            if (have_rows('cbis_api_keys', 'option')):
                while (have_rows('cbis_api_keys', 'option')): the_row();

            $location_categories = array();
            $location_categories[] = array('arena' => 1, 'cbis_location_cat_id' => get_sub_field('cbis_event_id'), 'cbis_location_name' => 'arena');
            if (! empty(get_sub_field('cbis_location_ids'))) {
                foreach (get_sub_field('cbis_location_ids') as $location) {
                    $location_categories[] = array(
                            'arena'                 => 0,
                            'cbis_location_cat_id'  => $location['cbis_location_cat_id'],
                            'cbis_location_name'    => $location['cbis_location_name']
                        );
                }
            }

            $cbis_keys[] = array(
                    'cbis_key'           => get_sub_field('cbis_api_product_key'),
                    'cbis_geonode'       => get_sub_field('cbis_api_geonode_id'),
                    'cbis_event_id'      => get_sub_field('cbis_event_id'),
                    'cbis_exclude'       => get_sub_field('cbis_filter_categories'),
                    'cbis_groups'        => get_sub_field('cbis_publishing_groups'),
                    'cbis_locations'     => $location_categories
                );
            endwhile;
            endif;

            wp_localize_script('hbg-event-importer', 'cbis_ajax_vars', array('cbis_keys' => $cbis_keys));

            // Set XCAP API variables
            $xcap_keys = array();
            if (have_rows('xcap_api_urls', 'option')):
                while (have_rows('xcap_api_urls', 'option')): the_row();

            $xcap_keys[] = array(
                    'xcap_api_url'       => get_sub_field('xcap_api_url'),
                    'xcap_exclude'       => get_sub_field('xcap_filter_categories'),
                    'xcap_groups'        => get_sub_field('xcap_publishing_groups')
                );
            endwhile;
            endif;

            wp_localize_script('hbg-event-importer', 'xcap_ajax_vars', array('xcap_keys' => $xcap_keys));
        }
    }

    /**
     * Debug code
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
            }
        );

        add_submenu_page(
            null,
            __('Import CBIS locations', 'hbg-event-importer'),
            __('Import CBIS events', 'hbg-event-importer'),
            'edit_posts',
            'import-cbis-locations',
            function () {
                new \HbgEventImporter\Parser\CbisLocation('http://api.info.citybreak.com/Products.asmx?wsdl');
            }
        );

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
            }
        );
    }

    /**
     * TA BORT
     * Creates an admin page that executes event import
     * @return void
     */
    public function addCronParserPage()
    {
        add_submenu_page(
            null,
            'import-data',
            'import-data',
            'edit_posts',
            'cron-import',
            function () {
                if (get_field('xcap_daily_cron', 'option') == true) {
                    ?>
                    <script type="text/javascript">
                        (function($) {
                            // Import events from XCAP
                            var data = {action:'import_events', value:'xcap', api_keys:'', cron:true};
                            ImportEvents.Parser.Eventhandling.parseEvents(data);
                        })(jQuery);
                    </script>
                <?php

                }
                if (get_field('cbis_daily_cron', 'option') == true) {
                    ?>
                    <script type="text/javascript">
                        (function($) {
                            // Import events from CBIS
                            var data = {action:'import_events', value:'cbis', api_keys:'', cron:true};
                            ImportEvents.Parser.Eventhandling.parseEvents(data);
                            // Import arenas/locations from CBIS
                            var data = {action:'import_events', value:'', api_keys:'', cron:true};
                            ImportEvents.Parser.Eventhandling.parseCbislocation(data);
                        })(jQuery);
                    </script>
                <?php

                }
            });
    }

    /**
     * Starts the data import
     * @return void
     */
    public static function startImport()
    {
        file_put_contents(dirname(__FILE__)."/log/cron_import.log", "Cron last run: " . date("Y-m-d H:i:s"));
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
     * Creates custom database table on plugin activation
     */
    public static function initDatabaseTable()
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

    /**
     * Add custom user roles
     * @return void
     */
    public static function initUserRoles()
    {
        add_role('event_contributor', __("Event contributor", 'event-manager'), array(
            'read' => true,
            'edit_posts' => true,
            'delete_posts' => true,
            'edit_published_posts' => true,
            'upload_files' => true,
            'edit_others_posts' => true,
        ));
    }
}
