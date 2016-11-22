<?php

namespace HbgEventImporter\Entity;

use \HbgEventImporter\Helper\DataCleaner as DataCleaner;

abstract class CustomPostType
{
    protected $namePlural;
    protected $nameSingular;
    protected $slug;
    protected $args;

    public $tableColumns = array();
    public $tableSortableColumns = array();
    public $tableColumnsContentCallback = array();

    /**
     * Registers a custom post type
     * @param string $namePlural   Post type name in plural
     * @param string $nameSingular Post type name in singular
     * @param string $slug         Post type slug
     * @param array  $args         Post type arguments
     */

    public function __construct($namePlural, $nameSingular, $slug, $args = array())
    {
        $this->namePlural = $namePlural;
        $this->nameSingular = $nameSingular;
        $this->slug = $slug;
        $this->args = $args;

        // Register post type on init
        add_action('init', array($this, 'registerPostType'));
        add_filter('manage_edit-' . $this->slug . '_columns', array($this, 'tableColumns'));
        add_filter('manage_edit-' . $this->slug . '_sortable_columns', array($this, 'tableSortableColumns'));
        add_action('manage_' . $this->slug . '_posts_custom_column', array($this, 'tableColumnsContent'), 10, 2);
        add_action('wp_ajax_my_action', array($this, 'acceptOrDeny'));
        add_action('wp_ajax_collect_occasions', array($this, 'collectOccasions'));
        add_action('wp_ajax_import_events', array($this, 'importEvents'));
        add_action('wp_ajax_dismiss', array($this, 'dismissInstructions'));
        add_action('admin_head', array($this, 'removeMedia'));
        add_filter('get_sample_permalink_html', array($this, 'replacePermalink'), 10, 5);
        add_filter('redirect_post_location', array($this, 'redirectLightboxLocation'), 10, 2);
        add_filter('post_updated_messages', array($this, 'postPublishedMsg'));
    }

    /**
     * Replaces permalink on edit post with API-url
     * @return string
     */
    public function replacePermalink($return, $post_id, $new_title, $new_slug, $post)
    {
        $postType = $post->post_type;
        if ($postType == 'page') {
            return $return;
        }
        $jsonUrl = home_url().'/json/wp/v2/'.$postType.'/';
        $apiUrl = $jsonUrl.$post_id;
        return '<strong>'.__('API-url', 'event-manager').':</strong> <a href="'.$apiUrl.'" target="_blank">'.$apiUrl.'</a>';
    }

    /**
     * Remove "Add media" button from posts
     */
    public function removeMedia()
    {
        global $current_screen;
        if ($current_screen->post_type != 'page') {
            remove_action('media_buttons', 'media_buttons');
        }
    }

    /**
     * Start parsing event importer
     */
    public function importEvents()
    {
        if ($_POST['value'] == 'cbis') {
            $importer = new \HbgEventImporter\Parser\CBIS('http://api.cbis.citybreak.com/Products.asmx?wsdl');
            $data = $importer->getCreatedData();
            wp_send_json($data);
        } elseif ($_POST['value'] == 'xcap') {
            $importer = new \HbgEventImporter\Parser\Xcap('http://mittkulturkort.se/calendar/listEvents.action?month=&date=&categoryPermaLink=&q=&p=&feedType=ICAL_XML');
            $data = $importer->getCreatedData();
            wp_send_json($data);
        } elseif ($_POST['value'] == 'cbislocation') {
            $importer = new \HbgEventImporter\Parser\CbisLocation('http://api.cbis.citybreak.com/Products.asmx?wsdl');
            $data = $importer->getCreatedData();
            wp_send_json($data);
        }
    }

    /**
     * Saves event occasions to database if missing
     */
    public function collectOccasions()
    {
        global $wpdb;

        $sql = $wpdb->prepare("SELECT ID FROM " . $wpdb->posts . " WHERE post_status = %s AND post_type = %s", 'publish', 'event');
        $result = $wpdb->get_results($sql);

        $resultString = "";

        foreach ($result as $key => $event) {
            $occasions = get_field('occasions', $event->ID);
            $db_occasions = $wpdb->prefix . "occasions";
            foreach ($occasions as $newKey => $value) {
                $timestamp = strtotime($value['start_date']);
                $timestamp2 = strtotime($value['end_date']);
                $timestamp3 = (empty($value['door_time'])) ? null : strtotime($value['door_time']);
                if ($timestamp <= 0 || $timestamp2 <= 0 || $timestamp == false || $timestamp2 == false) {
                    continue;
                }
                $testQuery = $wpdb->prepare("SELECT * FROM $db_occasions WHERE event = %d AND timestamp_start = %d AND timestamp_end = %d", $event->ID, $timestamp, $timestamp2);
                $existing = $wpdb->get_results($testQuery);
                if (empty($existing)) {
                    $newId = $wpdb->insert($db_occasions, array('event' => $event->ID, 'timestamp_start' => $timestamp, 'timestamp_end' => $timestamp2, 'timestamp_door' => $timestamp3));
                    $resultString .= "New event occasions inserted with event id: " . $event->ID . ', and timestamp_start: ' . $timestamp . ", timestamp_end: " . $timestamp2 . ", timestamp_door: " . $timestamp3 ."\n";
                } else {
                    $resultString .= "Already exists! Event: " . $existing[0]->event . ', timestamp_start: ' . $existing[0]->timestamp_start . ", timestamp_end: " . $existing[0]->timestamp_end . "\n";
                }
            }
        }
        ob_clean();
        // Print results to console
        // echo $resultString;
        wp_die();
    }

    /**
     * Hides instruction notice if dismissed.
     */
    public function dismissInstructions()
    {
        $current_user = wp_get_current_user();
        $user_id = $current_user->ID;
        add_user_meta( $user_id, 'dismissed_instr', 1, true );
    }

    /**
     * Creates a meta value (accepted) for post with value -1, 0 or 1 if, updates if meta value already exists for that post
     * @return int $newValue
     */
    public function acceptOrDeny()
    {
        if (!isset($_POST['postId']) || !isset($_POST['value'])) {
            ob_clean();
            echo _e('Something went wrong!', 'event-manager');
            wp_die();
        }
        $postId =  $_POST['postId'];
        $newValue = $_POST['value'];

        $postAccepted = get_post_meta($postId, 'accepted');

        $post = get_post($postId);
        if ($newValue == -1) {
            $post->post_status = 'draft';
        }
        if ($newValue == 1) {
            $post->post_status = 'publish';
        }

        $error = wp_update_post($post, true);

        if ($postAccepted == false) {
            add_post_meta($postId, 'accepted', $newValue);

            ob_clean();
            echo $newValue;
            wp_die();
        } else {
            if ($postAccepted[0] == $newValue) {
                ob_clean();
                echo $postAccepted[0];
                wp_die();
            }
            update_post_meta($postId, 'accepted', $newValue);
            ob_clean();
            echo $newValue;
            wp_die();
        }
    }

    /**
     * Registers the post type with WP
     * @return string Post type slug
     */
    public function registerPostType()
    {
        $labels = array(
            'name'                => $this->nameSingular,
            'singular_name'       => $this->nameSingular,
            'add_new'             => sprintf(__('Add new %s', 'event-manager'), $this->nameSingular),
            'add_new_item'        => sprintf(__('Add new %s', 'event-manager'), $this->nameSingular),
            'edit_item'           => sprintf(__('Edit %s', 'event-manager'), $this->nameSingular),
            'new_item'            => sprintf(__('New %s', 'event-manager'), $this->nameSingular),
            'view_item'           => sprintf(__('View %s', 'event-manager'), $this->nameSingular),
            'search_items'        => sprintf(__('Search %s', 'event-manager'), $this->namePlural),
            'not_found'           => sprintf(__('No %s found', 'event-manager'), $this->namePlural),
            'not_found_in_trash'  => sprintf(__('No %s found in trash', 'event-manager'), $this->namePlural),
            'parent_item_colon'   => sprintf(__('Parent %s:', 'event-manager'), $this->nameSingular),
            'menu_name'           => $this->namePlural,
        );

        $this->args['labels'] = $labels;

        register_post_type($this->slug, $this->args);

        return $this->slug;
    }

    /**
     * Adds a column to the admin list table
     * @param string   $key             Column key
     * @param string   $title           Column title
     * @param boolean  $sortable        Sortable or not
     * @param callback $contentCallback Callback function for displaying
     *                                  column content (params: $columnKey, $postId)
     */
    public function addTableColumn($key, $title, $sortable = false, $contentCallback = false)
    {
        $this->tableColumns[$key] = $title;

        if ($sortable === true) {
            $this->tableSortableColumns[$key] = $key;
        }

        if ($contentCallback !== false) {
            $this->tableColumnsContentCallback[$key] = $contentCallback;
        }
    }

    /**
     * Set up table columns
     * @param  array $columns Default columns
     * @return array          New columns
     */
    public function tableColumns($columns)
    {
        if (!empty($this->tableColumns) && is_array($this->tableColumns)) {
            $columns = $this->tableColumns;
        }

        return $columns;
    }

    /**
     * Setup sortable columns
     * @param  array $columns Default columns
     * @return array          New columns
     */
    public function tableSortableColumns($columns)
    {
        if (!empty($this->tableSortableColumns) && is_array($this->tableSortableColumns)) {
            $columns = $this->tableColumns;
        }

        function arraytolower(array $columns, $round = 0)
        {
            return unserialize(strtolower(serialize($columns)));
        }

        return arraytolower($columns);
    }

    /**
     * Set table column content with callback functions
     * @param  string  $column Key of the column
     * @param  integer $postId Post id of the current row in table
     * @return void
     */
    public function tableColumnsContent($column, $postId)
    {
        if (!isset($this->tableColumnsContentCallback[$column])) {
            return;
        }

        call_user_func_array($this->tableColumnsContentCallback[$column], array($column, $postId));
    }

    /**
     * Redirect post if created within iframe.
     * @param  string  $location redirection url
     * @param  int     $post_id event post id
     */
    public function redirectLightboxLocation($location, $post_id)
    {
        $referer = (isset($_SERVER['HTTP_REFERER'])) ? $_SERVER['HTTP_REFERER'] : null;
        if ((isset($_GET['lightbox']) && $_GET['lightbox'] == 'true') || strpos($referer, 'lightbox=true') > -1) {
            if (isset($_POST['save']) || isset($_POST['publish'])) {
                return $location.'&lightbox=true';
            }
        }
        return $location;
    }

    /**
     * Update admin notice messages. Removes public links.
     * @return array
     */
    function postPublishedMsg( $messages )
    {
        foreach($messages as $key => $value)
        {
            $messages['post'][1]  = __('Post updated.', 'event-manager');
            $messages['post'][6]  = __('Post published.', 'event-manager');
            $messages['post'][8]  = __('Post submitted.', 'event-manager');
            $messages['post'][10] = __('Post draft updated.', 'event-manager');
        }
        return $messages;
    }

    /**
     * When publish are clicked we are either creating the meta 'accepted' with value 1 or update it
     * @param int $ID event post id
     * @param $post wordpress post object
     */
    public function setAcceptedOnPublish($ID, $post)
    {
        $metaAccepted = get_post_meta($ID, 'accepted', true);
        if (!isset($metaAccepted)) {
            add_post_meta($ID, 'accepted', 1);
        } else {
            update_post_meta($ID, 'accepted', 1);
        }
    }

    /**
     * Format phone number before save to dabtabase
     * @param  string $value   the value of the field
     * @param  int    $post_id the post id to save against
     * @param  array  $field   the field object
     * @return string          the new value
     */
    public function acfUpdatePhone($value, $post_id, $field)
    {
        $value = preg_replace("/[^0-9\-\+\(\)\s ]/", "", $value);
        $value = DataCleaner::phoneNumber($value);
        return $value;
    }
}
