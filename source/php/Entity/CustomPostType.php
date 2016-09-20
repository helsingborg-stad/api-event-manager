<?php

namespace HbgEventImporter\Entity;

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
    }

    public function importEvents()
    {
        if($_POST['value'] == 'cbis')
        {
            $importer = new \HbgEventImporter\Parser\CBIS('http://api.cbis.citybreak.com/Products.asmx?wsdl');
            $data = $importer->getCreatedData();
            wp_send_json($data);
        }
        else if($_POST['value'] == 'xcap')
        {
            $importer = new \HbgEventImporter\Parser\Xcap('http://mittkulturkort.se/calendar/listEvents.action?month=&date=&categoryPermaLink=&q=&p=&feedType=ICAL_XML');
            $data = $importer->getCreatedData();
            wp_send_json($data);
        }
    }

    public function collectOccasions()
    {
        global $wpdb;

        $sql = $wpdb->prepare("SELECT ID FROM " . $wpdb->posts . " WHERE post_status = %s AND post_type = %s", 'publish', 'event');
        $result = $wpdb->get_results($sql);

        $resultString = "";

        foreach ($result as $key => $event) {
            $occasions = get_field('occasions', $event->ID);
            foreach($occasions as $newKey => $value) {
                $timestamp = strtotime($value['start_date']);
                $timestamp2 = strtotime($value['end_date']);
                $timestamp3 = strtotime($value['door_time']);
                if($timestamp <= 0 || $timestamp2 <= 0 || $timestamp == false || $timestamp2 == false || $timestamp2 < $timestamp)
                    continue;
                $testQuery = $wpdb->prepare("SELECT * FROM $db_occasions WHERE event = %d AND timestamp_start = %d AND timestamp_end = %d", $event->ID, $timestamp, $timestamp2);
                $existing = $wpdb->get_results($testQuery);
                if(empty($existing))
                {
                    $newId = $wpdb->insert($db_occasions, array('event' => $event->ID, 'timestamp_start' => $timestamp, 'timestamp_end' => $timestamp2, 'timestamp_door' => $timestamp3));
                    $resultString .= "New event occasions inserted with event id: " . $event->ID . ', and timestamp_start: ' . $timestamp . ", timestamp_end: " . $timestamp2 . "\n";
                }
                else
                    $resultString .= "Already exists! Event: " . $existing[0]->event . ', timestamp_start: ' . $existing[0]->timestamp_start . ", timestamp_end: " . $existing[0]->timestamp_end . "\n";
            }

        }
        ob_clean();
        echo $resultString;
        wp_die();
    }

    /**
     * Creates a meta value (accepted) for post with value -1, 0 or 1 if, updates if meta value already exists for that post
     * @return int $newValue
     */
    public function acceptOrDeny()
    {
        if(!isset($_POST['postId']) || !isset($_POST['value']))
        {
            ob_clean();
            echo "Something went wrong!";
            wp_die();
        }
        $postId =  $_POST['postId'];
        $newValue = $_POST['value'];

        $postAccepted = get_post_meta($postId, 'accepted');

        $post = get_post($postId);
        if($newValue == -1)
            $post->post_status = 'draft';
        if($newValue == 1)
            $post->post_status = 'publish';

        $error = wp_update_post($post, true);

        if($postAccepted == false) {
            add_post_meta($postId, 'accepted', $newValue);

            ob_clean();
            echo $newValue;
            wp_die();
        } else {

            if($postAccepted[0] == $newValue) {
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
            'add_new'             => sprintf(__('Add New %s', 'event-manager'), $this->nameSingular),
            'add_new_item'        => sprintf(__('Add New %s', 'event-manager'), $this->nameSingular),
            'edit_item'           => sprintf(__('Edit %s', 'event-manager'), $this->nameSingular),
            'new_item'            => sprintf(__('New %s', 'event-manager'), $this->nameSingular),
            'view_item'           => sprintf(__('View %s', 'event-manager'), $this->nameSingular),
            'search_items'        => sprintf(__('Search %s', 'event-manager'), $this->namePlural),
            'not_found'           => sprintf(__('No %s found', 'event-manager'), $this->namePlural),
            'not_found_in_trash'  => sprintf(__('No %s found in Trash', 'event-manager'), $this->namePlural),
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

        return array_map('strtolower', $columns);
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
}
