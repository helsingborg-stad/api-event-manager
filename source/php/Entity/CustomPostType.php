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
        add_action('admin_enqueue_scripts', array($this, 'addCustomJS'));
    }

    public function testing($classes)
    {
        $postAndId = explode('-', $classes[3]);
        if($postAndId[0] == 'post')
        {
            $metaAccepted = get_post_meta($postAndId[1], 'accepted');
            if($metaAccepted[0] == -1)
                $classes[] = "red";
            else if($metaAccepted[0] == 1)
                $classes[] = "green";
        }
        return $classes;
    }

    public function addPostAction()
    {
        add_filter('post_class', array($this, 'testing'));
    }

    public function acceptOrDeny()
    {
        $postId =  $_POST['postId'];
        $newValue = $_POST['value'];

        $postAccepted = get_post_meta($postId, 'accepted');

        if($postAccepted == false)
        {
            add_post_meta($postId, 'accepted', $newValue);

            ob_clean();
            echo $newValue;
            wp_die();
        }
        else
        {
            if($postAccepted[0] == $newValue)
            {
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

    public function addCustomJS($hook)
    {
        wp_enqueue_script('addCustomJS', HBGEVENTIMPORTER_URL . "/source/js/custom.js");
        wp_enqueue_style('addCustomCss', HBGEVENTIMPORTER_URL . "/dist/css/hbg-event-importer.min.css");
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

        return $columns;
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
