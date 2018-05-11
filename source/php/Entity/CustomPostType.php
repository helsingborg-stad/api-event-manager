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

        add_action('wp_ajax_accept_or_deny', array($this, 'acceptAndDeny'));
        add_action('wp_ajax_collect_occasions', array($this, 'collectOccasions'));
        add_action('wp_ajax_import_events', array($this, 'importEvents'));
        add_action('wp_ajax_dismiss', array($this, 'dismissInstructions'));
        add_action('admin_head', array($this, 'removeMedia'));
        add_filter('get_sample_permalink_html', array($this, 'replacePermalink'), 10, 5);
        add_filter('redirect_post_location', array($this, 'redirectLightboxLocation'), 10, 2);
        add_filter('post_updated_messages', array($this, 'postPublishedMsg'));
        add_action('admin_menu', array($this, 'removePublishBox'));
        add_filter('acf/update_value/name=user_groups', array($this, 'updateuserGroups'), 10, 3);
    }

    /**
     * Start parsing event importer
     */
    public function importEvents()
    {
        if ($_POST['value'] == 'cbis') {
            $api_keys = $_POST['api_keys'];
            $importer = new \HbgEventImporter\Parser\CbisEvent('http://api.cbis.citybreak.com/Products.asmx?wsdl', $api_keys);
            $data = $importer->getCreatedData();
            wp_send_json($data);
        } elseif ($_POST['value'] == 'cbislocation') {
            $api_keys = $_POST['api_keys'];
            $location = $_POST['cbis_location'];
            $importer = new \HbgEventImporter\Parser\CbisLocation('http://api.cbis.citybreak.com/Products.asmx?wsdl', $api_keys, $location);
            $data = $importer->getCreatedData();
            wp_send_json($data);
        } elseif ($_POST['value'] == 'xcap') {
            $api_keys = $_POST['api_keys'];
            $importer = new \HbgEventImporter\Parser\Xcap($api_keys['xcap_api_url'], $api_keys);
            $data = $importer->getCreatedData();
            wp_send_json($data);
        } elseif ($_POST['value'] == 'transticket') {
            $api_keys = $_POST['api_keys'];
            $importer = new \HbgEventImporter\Parser\TransTicket($api_keys['transticket_api_url'], $api_keys);
            $data = $importer->getCreatedData();
            wp_send_json($data);
        } elseif ($_POST['value'] == 'arcgislocation') {
            $api_keys = $_POST['api_keys'];
            $importer = new \HbgEventImporter\Parser\Arcgis($api_keys['arcgis_api_url'], $api_keys);
            $data = $importer->getCreatedData();
            wp_send_json($data);
        }
    }

    /**
     * Replaces permalink on edit post with API-url
     * @return string
     */
    public function replacePermalink($return, $post_id, $new_title, $new_slug, $post)
    {
        $postType = $post->post_type;
        $jsonUrl = home_url() . '/json/wp/v2/' . $postType . '/';
        $apiUrl = $jsonUrl . $post_id;

        return '<strong>' . __('API-url', 'event-manager') . ':</strong> <a href="' . $apiUrl . '" target="_blank">' . $apiUrl . '</a>';
    }

    /**
     * Remove "Add media" button from posts
     * @return void
     */
    public function removeMedia()
    {
        global $current_screen;

        if ($current_screen->post_type != 'page') {
            remove_action('media_buttons', 'media_buttons');
        }
    }

    /**
     * Saves event occasions to database if missing
     * @return void
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
                $timestampStart = strtotime($value['start_date']);
                $timestampEnd = strtotime($value['end_date']);
                $timestampDoor = (empty($value['door_time'])) ? null : strtotime($value['door_time']);

                if ($timestampStart <= 0 || $timestampEnd <= 0 || $timestampStart == false || $timestampEnd == false) {
                    continue;
                }

                $testQuery = $wpdb->prepare("SELECT * FROM $db_occasions WHERE event = %d AND timestamp_start = %d AND timestamp_end = %d", $event->ID, $timestampStart, $timestampEnd);
                $existing = $wpdb->get_results($testQuery);

                if (empty($existing)) {
                    $newId = $wpdb->insert($db_occasions, array('event' => $event->ID, 'timestamp_start' => $timestampStart, 'timestamp_end' => $timestampEnd, 'timestamp_door' => $timestampDoor));
                    $resultString .= "New event occasions inserted with event id: " . $event->ID . ', and timestamp_start: ' . $timestampStart . ", timestamp_end: " . $timestampEnd . ", timestamp_door: " . $timestampDoor . "\n";
                } else {
                    $resultString .= "Already exists! Event: " . $existing[0]->event . ', timestamp_start: ' . $existing[0]->timestamp_start . ", timestamp_end: " . $existing[0]->timestamp_end . "\n";
                }
            }
        }

        if (ob_get_contents()) {
            ob_end_clean();
        }

        wp_die();
    }

    /**
     * Hides instruction notice if dismissed.
     * @return void
     */
    public function dismissInstructions()
    {
        $current_user = wp_get_current_user();
        $user_id = $current_user->ID;

        add_user_meta($user_id, 'dismissed_instr', 1, true);
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
     * @param  string $location redirection url
     * @param  int    $post_id  event post id
     */
    public function redirectLightboxLocation($location, $post_id)
    {
        $referer = (isset($_SERVER['HTTP_REFERER'])) ? $_SERVER['HTTP_REFERER'] : null;

        if ((isset($_GET['lightbox']) && $_GET['lightbox'] == 'true') || strpos($referer, 'lightbox=true') > -1) {
            if (isset($_POST['save']) || isset($_POST['publish'])) {
                return $location . '&lightbox=true';
            }
        }

        return $location;
    }

    /**
     * Update admin notice messages. Removes public links.
     * @return array
     */
    public function postPublishedMsg($messages)
    {
        foreach ($messages as $key => $value) {
            $messages['post'][1] = __('Post updated.', 'event-manager');
            $messages['post'][6] = __('Post published.', 'event-manager');
            $messages['post'][8] = __('Post submitted.', 'event-manager');
            $messages['post'][10] = __('Post draft updated.', 'event-manager');
        }

        return $messages;
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

    /**
     * Remove submit buttons on post if user don't have access
     * @return void
     */
    public function removePublishBox()
    {
        $post_id = (isset($_GET['post'])) ? $_GET['post'] : false;
        // Get current post object
        $post = get_post($post_id);
        $user = wp_get_current_user();

        // Return if user is admin/editor or if user is 'post_author'
        if (current_user_can('administrator') || current_user_can('editor') || current_user_can('guide_administrator') || $post_id == false || $post->post_author == $user->ID) {
            return;
        }

        $post_types = get_field('event_group_select', 'option') ? get_field('event_group_select', 'option') : array();

        if ($post != null && in_array($post->post_type, $post_types)) {
            // Get posts group taxonomies
            $post_terms = wp_get_post_terms($post_id, 'user_groups', array("fields" => "ids"));

            // Get users groups
            $user_groups = \HbgEventImporter\Admin\FilterRestrictions::getTermChildren(get_current_user_id());

            // Remove publish capability if user don't exist in a group
            if (empty($user_groups) || !is_array($user_groups)) {
                add_action('admin_notices', array($this, 'missingAccessNotice'));
                remove_meta_box('submitdiv', $this->slug, 'side');
                return;
            }

            // Check if user belongs to any of the events groups. Remove publish capability if not.
            $result = array_intersect($post_terms, $user_groups);
            if (count($result) < 1) {
                add_action('admin_notices', array($this, 'missingAccessNotice'));
                remove_meta_box('submitdiv', $this->slug, 'side');
            }
        }

        return;
    }

    /**
     * Notice to inform if the user don't have access to the event.
     * @return void
     */
    public function missingAccessNotice()
    {
        $screen = get_current_screen();

        if ($screen->post_type !== $this->slug) {
            return;
        }

        echo '<div class="notice notice-warning dismissable is-dismissible">';
        echo '<p>' . __("You don't have access to edit this event. Contact an administrator for further information.", "event-manager") . '</p>';
        echo '</div>';
    }

    /**
     * Function to save publishing groups correctly,
     * since contributors dont have access to all groups we must add/remove new groups to the existing table value.
     * @param  string $value   the value of the field
     * @param  int    $post_id the post id to save against
     * @param  array  $field   the field object
     * @return array           the new value
     */
    public function updateuserGroups($value, $post_id, $field)
    {
        if (current_user_can('administrator') || current_user_can('editor') || current_user_can('guide_administrator')) {
            return $value;
        }

        // Get users groups
        $current_user = wp_get_current_user();
        $id = 'user_' . $current_user->ID;
        $user_groups = get_field('event_user_groups', $id);

        // Get posts groups
        $post_groups = get_field('user_groups', $post_id);

        if (!$post_groups) {
            return $value;
        }

        // Convert strings to int
        $new_value = array_map('intval', $value);

        // Get the empty values
        $empty_values = array_diff($user_groups, $new_value);

        // Remove empty values from post groups
        $new_post_groups = array_diff($post_groups, $empty_values);

        // Add newly added groups to existing post groups
        $new_array = array_merge($new_post_groups, $new_value);

        // Remove duplicates
        $new_array = array_unique($new_array);

        return $new_array;
    }

    /**
     * Add public table column
     * @param array $columns array with table columns
     */
    public function addAcceptDenyTable($columns)
    {
        if (current_user_can('administrator') || current_user_can('editor')) {
            $columns['acceptAndDeny'] = __('Public', 'myplugindomain');
        }

        return $columns;
    }

    /**
     * Adds accept and deny buttons to public table
     * @param string $column_name name of column
     * @param int    $post_id
     */
    public function addAcceptDenyButtons($column_name, $post_id)
    {
        if ('acceptAndDeny' != $column_name) {
            return;
        }

        $post_status = get_post_status($post_id);

        $first = '';
        $second = '';

        if ($post_status == 'publish') {
            $first = 'hidden';
        } elseif ($post_status == 'draft' || $post_status == 'trash' || $post_status == 'pending') {
            $second = 'hidden';
        }

        // If post is created from an external client we must publish it manually
        $revisions = wp_get_post_revisions($post_id);
        $consumer_client = get_post_meta($post_id, 'consumer_client');

        if (!empty($consumer_client) && $post_status == 'draft' && empty($revisions)) {
            echo '<a href="' . get_edit_post_link($post_id) . '" class="button" title="' . __('This post must be updated before it can be published.', 'event-manager') . '">' . __('Edit draft', 'event-manager') . '</a>';
        } else {
            // Show accept or deny buttons
            echo '<a href="#" class="accept button-primary ' . $first . '" post-id="' . $post_id . '">' . __('Accept', 'event-manager') . '</a>
            <a href="#" class="deny button-primary ' . $second . '" post-id="' . $post_id . '">' . __('Deny', 'event-manager') . '</a>';
        }
    }

    /**
     * Accept or deny an event. Changes post status to draft if denied.
     * @return int $value
     */
    public function acceptAndDeny()
    {
        if (!isset($_POST['postId']) || !isset($_POST['value'])) {
            echo __('Something went wrong!', 'event-manager');
            wp_die();
        }

        $postId = $_POST['postId'];
        $value = $_POST['value'];

        $post = get_post($postId);
        if ($value == 0) {
            $post->post_status = 'draft';
        }
        if ($value == 1) {
            $post->post_status = 'publish';
        }

        $update = wp_update_post($post, true);
        if (is_wp_error($update)) {
            echo __('Error', 'event-manager');
            wp_die();
        }

        echo $value;
        wp_die();
    }
}
