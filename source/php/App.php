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

        // Add theme support.
        add_action('after_setup_theme', array($this, 'themeSupport'));
        
        // Admin scripts
        add_action('admin_enqueue_scripts', array($this, 'enqueueStyles'));
        add_action('admin_enqueue_scripts', array($this, 'enqueueScripts'));

        // Redirects
        add_filter('login_redirect', array($this, 'loginRedirect'), 10, 3);
        add_action('admin_init', array($this, 'dashboardRedirect'));

        // Load acf plugins
        add_action('init', function () {
            require_once HBGEVENTIMPORTER_PATH . 'source/php/Vendor/acf-unique-id/acf-unique_id-v5.php';
        });

        // Acf setting: Google API key
        add_action('acf/init', array($this, 'acfGoogleKey'));

        // Create custom ACF location rule. Adds 'user groups' to selected post types
        add_filter('acf/location/rule_types', array($this, 'acfLocationRulesTypes'));
        add_filter('acf/location/rule_values/settings', array($this, 'acfLocationRuleValues'));
        add_filter('acf/location/rule_match/settings', array($this, 'acfLocationRulesMatch'), 10, 3);

        //Init cron tasks
        new Cron();

        //Init translation extension configuration
        new Translation();

        //Init post types
        new PostTypes\Events();
        new PostTypes\Locations();
        new PostTypes\Organizers();
        new PostTypes\Sponsors();
        new PostTypes\Packages();
        new PostTypes\MembershipCards();
        new PostTypes\Guides();
        new PostTypes\Recommendations();

        new Taxonomy\EventCategories();
        new Taxonomy\UserGroups();
        new Taxonomy\EventTags();
        new Taxonomy\LocationCategories();
        new Taxonomy\GuideCategories();
        new Taxonomy\GuideNavigation();
        new Taxonomy\GuideType(); //DEPRICATED
        new Taxonomy\PointProperty();

        new Admin\Options();
        new Admin\UI();
        new Admin\FilterRestrictions();
        new Admin\UserRoles();
        new Admin\FileUploads();

        new Api\Filter();
        new Api\PostTypes();
        new Api\Taxonomies();
        new Api\Linking();

        new Api\EventFields();
        new Api\LocationFields();
        new Api\OrganizerFields();
        new Api\SponsorFields();
        new Api\PackageFields();
        new Api\MembershipCardFields();
        new Api\GuideFields();
        new Api\UserGroupFields();
        new Api\PointPropertyFields();
        new Api\NavigationFields();
        new Api\RecommendationFields();
    }

    /**
     * Add theme support
     */
    public function themeSupport()
    {
        add_theme_support('menus');
        add_theme_support('post-thumbnails');
        add_theme_support('html5');
        add_theme_support(
            'post-formats',
            array(
                'aside',
                'gallery',
                'link',
                'image',
                'quote',
                'status',
                'video',
                'audio',
                'chat'
            )
        );
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
        if (!is_wp_error($user)) {
            if (user_can($user->ID, 'edit_events')) {
                $redirect_to = admin_url('edit.php?post_type=event');
            } elseif (user_can($user->ID, 'edit_guides')) {
                $redirect_to = admin_url('edit.php?post_type=guide');
            }
        }

        return $redirect_to;
    }

    /**
     * Redirect user when entering dashboard.
     * @return void
     */
    public function dashboardRedirect()
    {
        global $pagenow;
        if ($pagenow !== 'index.php') {
            return;
        }

        if (isset($_GET['page']) && in_array($_GET['page'], array('acf-upgrade'))) {
            return;
        }

        if (current_user_can('edit_events')) {
            wp_redirect(admin_url('edit.php?post_type=event'), 301);
        } elseif(current_user_can('edit_guides')) {
            wp_redirect(admin_url('edit.php?post_type=guide'), 301);
        } else {
            return;
        }

        exit;
    }

    /**
     * Enqueue required style
     * @return void
     */
    public function enqueueStyles()
    {
        global $current_screen;

        if (in_array($current_screen->post_type, array('event', 'location', 'sponsor', 'package', 'membership-card', 'guide', 'organizer'))) {
            wp_enqueue_style('hbg-event-importer', HBGEVENTIMPORTER_URL . '/dist/css/app.min.css');
        }

        $referer = (isset($_SERVER['HTTP_REFERER'])) ? $_SERVER['HTTP_REFERER'] : null;

        if ((isset($_GET['lightbox']) && $_GET['lightbox'] == 'true') || strpos($referer, 'lightbox=true') > -1) {
            wp_enqueue_style('lightbox', plugins_url() . '/api-event-manager/dist/css/modal.min.css', false, '1.0.0');
        }

        if (isset($current_screen->post_type) && $current_screen->post_type === 'event') {
            wp_enqueue_style('jquery-ui', '//code.jquery.com/ui/1.11.4/themes/smoothness/jquery-ui.min.css');
        }
    }

    /**
     * Enqueue required scripts
     * @return void
     */
    public function enqueueScripts()
    {
        global $current_screen;

        if (isset($current_screen->post_type) && $current_screen->post_type === 'event') {
            wp_enqueue_script('jquery-ui-datepicker');
        }

        $acceptedPostTypes = array(
            'event',
            'location',
            'organizer',
            'sponsor',
            'package',
            'membership-card',
            'guide',
            'term',
        );

        if (is_object($current_screen) && in_array($current_screen->post_type, $acceptedPostTypes)) {
            wp_enqueue_script('hbg-event-importer', HBGEVENTIMPORTER_URL . '/dist/js/app.min.js');
        }

        wp_localize_script('hbg-event-importer', 'eventmanager', array(
            'ajaxurl'               => admin_url('admin-ajax.php'),
            'wpapiurl'              => home_url('json'),
            'adminurl'              => get_admin_url(),
            'require_title'         => __("\"Title\" is missing", 'event-manager'),
            'require_image'         => __("\"Featured\" image is missing", 'event-manager'),
            'new_organizer'         => __("Create new organizer", 'event-manager'),
            'new_sponsor'           => __("Create new sponsor", 'event-manager'),
            'new_location'          => __("Create new location", 'event-manager'),
            'new_card'              => __("Create new membership card", 'event-manager'),
            'new_membership-card'   => __("Create new membership card", 'event-manager'),
            'close'                 => __("Close", 'event-manager'),
            'add_images'            => __("Add images", 'event-manager'),
            'new_data_imported'     => __("Imported data", 'event-manager'),
            'events'                => __("Events", 'event-manager'),
            'locations'             => __("Locations", 'event-manager'),
            'organizers'            => __("Organizers", 'event-manager'),
            'time_until_reload'     => __("Time until reload", 'event-manager'),
            'loading'               => __("Loading", 'event-manager'),
            'choose_time'           => __("Choose time", 'event-manager'),
            'time'                  => __("Time", 'event-manager'),
            'hour'                  => __("Hour", 'event-manager'),
            'minute'                => __("Minute", 'event-manager'),
            'done'                  => __("Done", 'event-manager'),
            'now'                   => __("Now", 'event-manager'),
            'with_similar_name'     => __("with similar name", 'event-manager'),
            'sponsors'              => __("Sponsors", 'event-manager'),
            'packages'              => __("Packages", 'event-manager'),
            'membership_cards'      => __("Membership cards", 'event-manager'),
            'guides'                => __("Guides", 'event-manager'),
            'yes'                   => __("Yes", 'event-manager'),
            'no'                    => __("No", 'event-manager'),
            'confirm_statements'    => __("To upload an image, you need to confirm the statements below", 'event-manager'),
            'promote_event'         => __("I have the right to use this image to promote this event.", 'event-manager'),
            'identifiable_persons'  => __("Are there identifiable persons on the image/images?", 'event-manager'),
            'persons_approve'       => __("They have accepted that the image is used to promote this event and have been informed that after the image has been added to the database, it may appear in different channels to promote the event.", 'event-manager'),
            'duplicate_occasion'    => __("Duplicate occasion", 'event-manager'),
            'import_scheduled'      => __("Import scheduled", 'event-manager'),
        ));
    }

    /**
     * Creates occasions database table on plugin activation
     * @return void
     */
    public static function initDatabaseTable()
    {
        global $wpdb;
        global $event_db_version;

        $table_name = $wpdb->prefix . 'occasions';
        $charset_collate = $wpdb->get_charset_collate();

        $sql = "
            CREATE TABLE $table_name (
                ID BIGINT(20) UNSIGNED NOT NULL AUTO_INCREMENT,
                event BIGINT(20) UNSIGNED NOT NULL,
                timestamp_start BIGINT(20) UNSIGNED NOT NULL,
                timestamp_end BIGINT(20) UNSIGNED NOT NULL,
                timestamp_door BIGINT(20) UNSIGNED DEFAULT NULL,
                PRIMARY KEY  (ID)
            ) $charset_collate;
        ";

        require_once(ABSPATH . 'wp-admin/includes/upgrade.php');
        dbDelta($sql);

        add_option('event_db_version', $event_db_version);
    }

    /**
     * ACF settings action
     * @return void
     */
    public function acfGoogleKey()
    {
        acf_update_setting('google_api_key', get_option('options_google_geocode_api_key'));
    }

    /**
     * Add new location rule type 'Group settings'
     * @param  array $choices Location rule types
     * @return array
     */
    public function acfLocationRulesTypes($choices)
    {
        $choices['Event']['settings'] = 'Group settings';
        return $choices;
    }

    /**
     * Location rule type choices
     * @param  array $choices Location rule choices
     * @return array
     */
    public function acfLocationRuleValues($choices)
    {
        return $choices['post_types'] = "Post types";
    }

    /**
     * Matching custom location rule
     * @param  boolean $match   If rule match or not
     * @param  array   $rule    Current rule that to match against
     * @param  array   $options Data about the current edit screen
     * @return boolean
     */
    public function acfLocationRulesMatch($match, $rule, $options)
    {
        if (!isset($options['post_id'])) {
            return $match;
        }

        $postType = $options['post_type'] ?? get_post_type($options['post_id']);
        $groups = get_field('event_group_select', 'option');

        if ($groups) {
            if ($rule['operator'] == "==") {
                $match = (!empty($postType) && in_array($postType, $groups) && $options['post_id'] > 0);
            } elseif ($rule['operator'] == "!=") {
                $match = (!empty($postType) && !in_array($postType, $groups) && $options['post_id'] > 0);
            }
        }

        return $match;
    }
}
