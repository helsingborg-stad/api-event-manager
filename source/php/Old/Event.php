<?php

namespace HbgEventImporter;

class Event extends \HbgEventImporter\Post
{
    public static function initPostType()
    {
        $icon = 'data:image/svg+xml;base64,PD94bWwgdmVyc2lvbj0iMS4wIiBlbmNvZGluZz0iaXNvLTg4NTktMSI/Pg0KPCEtLSBHZW5lcmF0b3I6IEFkb2JlIElsbHVzdHJhdG9yIDE2LjAuMCwgU1ZHIEV4cG9ydCBQbHVnLUluIC4gU1ZHIFZlcnNpb246IDYuMDAgQnVpbGQgMCkgIC0tPg0KPCFET0NUWVBFIHN2ZyBQVUJMSUMgIi0vL1czQy8vRFREIFNWRyAxLjEvL0VOIiAiaHR0cDovL3d3dy53My5vcmcvR3JhcGhpY3MvU1ZHLzEuMS9EVEQvc3ZnMTEuZHRkIj4NCjxzdmcgdmVyc2lvbj0iMS4xIiBpZD0iQ2FwYV8xIiB4bWxucz0iaHR0cDovL3d3dy53My5vcmcvMjAwMC9zdmciIHhtbG5zOnhsaW5rPSJodHRwOi8vd3d3LnczLm9yZy8xOTk5L3hsaW5rIiB4PSIwcHgiIHk9IjBweCINCgkgd2lkdGg9Ijk4NS4zMzNweCIgaGVpZ2h0PSI5ODUuMzM0cHgiIHZpZXdCb3g9IjAgMCA5ODUuMzMzIDk4NS4zMzQiIHN0eWxlPSJlbmFibGUtYmFja2dyb3VuZDpuZXcgMCAwIDk4NS4zMzMgOTg1LjMzNDsiDQoJIHhtbDpzcGFjZT0icHJlc2VydmUiPg0KPGc+DQoJPHBhdGggZD0iTTg2OC41NjUsNDkyLjhjLTQuNCwyMi4xMDEtMjQsMzguMi00Ny41LDM5LjJjLTcuNCwwLjMtMTMuNyw1LjctMTUuMTAxLDEzYy0xLjUsNy4zLDIuMiwxNC43LDguOSwxNy44DQoJCWMyMS4zLDEwLDMzLjIsMzIuNCwyOC43LDU0LjVsLTQuMiwyMWMtNS41LDI3LjctMzYuMTAxLDQ1LTYyLjksMzguNGMtNy41LTEuOC0xNS4yLTMuMi0yMi44LTQuN2MtMTEuMi0yLjItMjIuNC00LjUtMzMuNi02LjcNCgkJYy0xNC44MDEtMy0yOS42MDEtNS44OTktNDQuNC04Ljg5OWMtMTcuNi0zLjUtMzUuMy03LjEwMS01Mi45LTEwLjYwMWMtMTkuNjk5LTQtMzkuMzk5LTcuODk5LTU5LjEtMTEuODk5DQoJCWMtMjEtNC4yLTQyLjEtOC40LTYzLjEtMTIuN2MtMjEuNjAxLTQuMy00My4yLTguNy02NC43LTEzYy0yMS40LTQuMy00Mi43LTguNjAxLTY0LjEwMS0xMi45Yy0yMC4zOTktNC4xLTQwLjgtOC4yLTYxLjE5OS0xMi4zDQoJCWMtMTguNy0zLjctMzcuMy03LjUtNTYtMTEuMmMtMTYuMi0zLjItMzIuNC02LjUtNDguNS05LjdjLTEyLjktMi42LTI1LjgtNS4xOTktMzguOC03LjhjLTguOS0xLjgtMTcuODAxLTMuNi0yNi43LTUuMzk5DQoJCWMtNC4xMDEtMC44MDEtOC4yLTEuNy0xMi4zLTIuNWMtMC4yLDAtMC40LTAuMTAxLTAuNjAxLTAuMTAxYzIuMiwxMC40LDEuMiwyMS41LTMuNiwzMS45Yy0xMC4xMDEsMjEuOC0zMy42MDEsMzMuMi01Ni4yLDI4LjgNCgkJYy02LjctMS4zLTE0LDEuMi0xNi45LDcuNGwtOSwxOS41Yy0yLjg5OSw2LjE5OSwwLDEzLjM5OSw1LjMwMSwxNy42OTljMSwwLjgwMSw3MjEuOCwzMzMuMTAxLDcyMi45OTksMzMzLjQNCgkJYzYuNywxLjMsMTQtMS4yLDE2LjktNy40bDktMTkuNWMyLjktNi4xOTksMC0xMy4zOTktNS4zLTE3LjY5OWMtMTgtMTQuMzAxLTI0LjYwMS0zOS42MDEtMTQuNS02MS40YzEwLjEtMjEuOCwzMy42LTMzLjIsNTYuMi0yOC44DQoJCWM2LjY5OSwxLjMsMTQtMS4yLDE2Ljg5OS03LjRsOS0xOS41YzIuOS02LjIsMC0xMy4zOTktNS4zLTE3LjdjLTE4LTE0LjMtMjQuNi0zOS42LTE0LjUtNjEuMzk5czMzLjYtMzMuMiw1Ni4yLTI4LjgNCgkJYzYuNywxLjMsMTQtMS4yLDE2LjktNy40bDktMTkuNWMyLjg5OS02LjIsMC0xMy40LTUuMzAxLTE3LjdjLTE4LTE0LjMtMjQuNi0zOS42LTE0LjUtNjEuNGMxMC4xMDEtMjEuOCwzMy42MDEtMzMuMTk5LDU2LjItMjguOA0KCQljNi43LDEuMywxNC0xLjIsMTYuOS03LjM5OWw5Ljg5OS0yMS42MDFjMi45LTYuMiwwLjItMTMuNS02LTE2LjM5OWwtMTA3LjY5OS00OS43TDg2OC41NjUsNDkyLjh6Ii8+DQoJPHBhdGggZD0iTTkuNjY1LDQ4NS45YzEuMiwwLjYsNzc5LjMsMTU2LjY5OSw3ODAuNiwxNTYuNjk5YzYuODAxLTAuMywxMy40LTQuNSwxNC43LTExLjFsNC4yLTIxYzEuMy02LjctMy4xLTEzLjEtOS4zLTE2DQoJCWMtMjAuOC05LjgtMzMuMTAxLTMyLjgtMjguNC01Ni40YzQuNy0yMy42LDI1LTQwLjEsNDgtNDEuMWM2LjgtMC4zLDEzLjQtNC41LDE0LjctMTEuMWwzLjEtMTUuNGwxLjEwMS01LjcNCgkJYzEuMy02LjctMy4xMDEtMTMuMS05LjMtMTZjLTIwLjgwMS05LjgtMzMuMTAxLTMyLjgtMjguNC01Ni4zOTljNC43LTIzLjYwMSwyNS00MC4xMDEsNDgtNDEuMTAxYzYuOC0wLjMsMTMuNC00LjUsMTQuNy0xMS4xDQoJCWw0LjItMjFjMS4zLTYuNy0zLjEwMS0xMy4xLTkuMzAxLTE2Yy0yMC44LTkuOC0zMy4xLTMyLjgtMjguMzk5LTU2LjRjNC43LTIzLjYsMjUtNDAuMSw0OC00MS4xYzYuOC0wLjMsMTMuMzk5LTQuNSwxNC43LTExLjENCgkJbDQuNjk5LTIzLjNjMS4zMDEtNi43LTMtMTMuMi05LjY5OS0xNC41YzAsMC03ODEuOS0xNTYuOC03ODIuNy0xNTYuOGMtNS44LDAtMTAuOSw0LjEtMTIuMSw5LjlsLTQuNywyMy4zDQoJCWMtMS4zLDYuNywzLjEsMTMuMSw5LjMsMTZjMjAuOCw5LjgsMzMuMSwzMi44LDI4LjQsNTYuNGMtNC43LDIzLjYtMjUsNDAuMS00OCw0MS4xYy02LjgwMSwwLjMtMTMuNCw0LjUtMTQuNywxMS4xbC00LjIsMjENCgkJYy0xLjMsNi43LDMuMSwxMy4xLDkuMywxNmMyMC44LDkuOCwzMy4xMDEsMzIuOCwyOC40LDU2LjRjLTQuNywyMy42LTI1LDQwLjEtNDgsNDEuMWMtNi44LDAuMy0xMy40LDQuNS0xNC43LDExLjFsLTQuMiwyMQ0KCQljLTEuMyw2LjcsMy4xMDEsMTMuMSw5LjMsMTZjMjAuODAxLDkuOCwzMy4xMDEsMzIuOCwyOC40LDU2LjRjLTQuNywyMy42MDEtMjUsNDAuMTAxLTQ4LDQxLjEwMWMtNi44LDAuMy0xMy40LDQuNS0xNC43LDExLjENCgkJbC00LjIsMjFDLTAuOTM1LDQ3Ni43LDMuNDY0LDQ4Myw5LjY2NSw0ODUuOXogTTY3Ni4xNjUsMjI5LjZjMi43LTEzLjUsMTUuOS0yMi4zLDI5LjQtMTkuNnMyMi4zLDE1LjksMTkuNiwyOS40bC0zMywxNjQuMg0KCQlsLTIwLjMsMTAxLjJjLTIuNCwxMS45LTEyLjgsMjAuMTAxLTI0LjUsMjAuMTAxYy0xLjYwMSwwLTMuMy0wLjItNC45LTAuNWMtMTMuNS0yLjctMjIuMy0xNS45LTE5LjYtMjkuNGwyMi43LTExMi45TDY3Ni4xNjUsMjI5LjYNCgkJeiBNMjI1LjM2NSwxMzkuMWMyLjctMTMuNSwxNS45LTIyLjMsMjkuNC0xOS42czIyLjMsMTUuOSwxOS42LDI5LjRsLTExLjQsNTYuN2wtMTIuODk5LDY0LjNsLTEwLjQsNTEuOGwtMTguNSw5Mi42DQoJCWMtMi4zOTksMTEuOS0xMi44LDIwLjEwMS0yNC41LDIwLjEwMWMtMS42LDAtMy4zLTAuMi00Ljg5OS0wLjVjLTAuNy0wLjEwMS0xLjQtMC4zMDEtMi0wLjVjLTEyLjQtMy42MDEtMjAuMTAxLTE2LjEwMS0xNy41LTI4LjkNCgkJbDMuNjk5LTE4LjdsOS43LTQ4LjRMMjI1LjM2NSwxMzkuMXoiLz4NCjwvZz4NCjxnPg0KPC9nPg0KPGc+DQo8L2c+DQo8Zz4NCjwvZz4NCjxnPg0KPC9nPg0KPGc+DQo8L2c+DQo8Zz4NCjwvZz4NCjxnPg0KPC9nPg0KPGc+DQo8L2c+DQo8Zz4NCjwvZz4NCjxnPg0KPC9nPg0KPGc+DQo8L2c+DQo8Zz4NCjwvZz4NCjxnPg0KPC9nPg0KPGc+DQo8L2c+DQo8Zz4NCjwvZz4NCjwvc3ZnPg0K';

        $labels = array(
            'name'               => _x('Evenemang', 'post type name'),
            'singular_name'      => _x('Evenemang', 'post type singular name'),
            'menu_name'          => __('Evenemang'),
            'add_new'            => __('Lägg till nytt evenemang'),
            'add_new_item'       => __('Lägg till evenemang'),
            'edit_item'          => __('Redigera evenemang'),
            'new_item'           => __('Nytt evenemang'),
            'all_items'          => __('Alla evenemang'),
            'view_item'          => __('Visa evenemang'),
            'search_items'       => __('Sök evenemang'),
            'not_found'          => __('Inga evenemang att visa'),
            'not_found_in_trash' => __('Inga evenemang i papperskorgen')
        );

        $args = array(
            'labels'               =>   $labels,
            'description'          =>   'Events',
            'menu_icon'            =>   $icon,
            'public'               =>   true,
            'publicly_queriable'   =>   true,
            'show_ui'              =>   true,
            'show_in_nav_menus'    =>   true,
            'has_archive'          =>   true,
            'rewrite'              =>   array(
                'slug'       => 'event',
                'with_front' => false
            ),
            'hierarchical'          =>  false,
            'exclude_from_search'   =>  false,
            'taxonomies'            =>  array('event-types'),
            'supports'              =>  array('title', 'revisions', 'editor', 'thumbnail'),
            'register_meta_box_cb'  =>  '\HbgEventImporter\Event::cleanContacts'
        );

        add_thickbox();

        register_post_type('event', $args);

        // Daily cron
        if (!wp_next_scheduled('import_events_daily')) {
            wp_schedule_event(time(), 'daily', 'import_events_daily');
        }

        add_action('manage_posts_extra_tablenav', function ($which) {
            global $current_screen;

            if ($current_screen->id != 'edit-event' || $which != 'top') {
                return;
            }

            echo '<div class="alignleft actions">';
                echo '<a href="' . admin_url('options.php?page=import-events')
                        . '" class="button-primary" id="post-query-submit">Import now</a>';
                echo '<a href="' . admin_url('options.php?page=import-cbis-events')
                        . '" class="button-primary" id="post-query-submit">Import CBIS now</a>';
            echo '</div>';
        });

        // Setup table columns
        // Columns
        add_filter('manage_edit-event_columns', '\HbgEventImporter\Event::defineListColumns');

        // Sorting
        add_filter('manage_edit-event_sortable_columns', '\HbgEventImporter\Event::sortListColumns');

        // Column content
        add_action('manage_event_posts_custom_column', '\HbgEventImporter\Event::listColumnContent', 10, 2);

        // Register taxonomy
        self::registerTaxonomy();
    }

    /**
     * Removes ids of contacts that have been deleted
     * @return void
     */
    public static function cleanContacts()
    {
        global $post;
        $postContacts = get_field('contacts', $post->ID);

        $ids = array();
        if(!empty($postContacts))
        {
            foreach($postContacts as $s) {
                $ids[] = $s->ID;
            }
            update_field('contacts', $ids, $post->ID);
        }
    }

    /**
     * Create a taxonomy
     *
     * @uses  Inserts new taxonomy object into the list
     * @uses  Adds query vars
     *
     * @param string  Name of taxonomy object
     * @param array|string  Name of the object type for the taxonomy object.
     * @param array|string  Taxonomy arguments
     * @return null|WP_Error WP_Error if errors, otherwise null.
     */
    public static function registerTaxonomy()
    {
        $labels = array(
            'name'                  => _x('Event categories', 'Taxonomy plural name', 'hbg-event-importer'),
            'singular_name'         => _x('Event category', 'Taxonomy singular name', 'hbg-event-importer'),
            'search_items'          => __('Search Event Categories', 'hbg-event-importer'),
            'popular_items'         => __('Popular Event Categories', 'hbg-event-importer'),
            'all_items'             => __('All Event Categories', 'hbg-event-importer'),
            'parent_item'           => __('Parent Event Category', 'hbg-event-importer'),
            'parent_item_colon'     => __('Parent Event Category', 'hbg-event-importer'),
            'edit_item'             => __('Edit Event Category', 'hbg-event-importer'),
            'update_item'           => __('Update Event Category', 'hbg-event-importer'),
            'add_new_item'          => __('Add New Event Category', 'hbg-event-importer'),
            'new_item_name'         => __('New Event Category', 'hbg-event-importer'),
            'add_or_remove_items'   => __('Add or remove Event Categories', 'hbg-event-importer'),
            'choose_from_most_used' => __('Choose from most used Event Categories', 'hbg-event-importer'),
            'menu_name'             => __('Event Category', 'hbg-event-importer'),
        );

        $args = array(
            'labels'                => $labels,
            'public'                => true,
            'show_in_nav_menus'     => true,
            'show_admin_column'     => false,
            'hierarchical'          => false,
            'show_tagcloud'         => true,
            'show_ui'               => true,
            'query_var'             => true,
            'rewrite'               => true
        );

        register_taxonomy('event-types', array('event'), $args);
    }

    /**
     * Defines list columns
     * @param  array $columns Original columns
     * @return array          Custom columns
     */
    public static function defineListColumns($columns)
    {
        $columns = array(
            'cb'               => '<input type="checkbox">',
            'title'            => __('Event'),
            'startDate' => __('Start date'),
            'endDate'   => __('End date'),
            'date'             => __('Date created')
        );

        return $columns;
    }

    /**
     * Define sortable columns
     * @param  array $columns Original columns
     * @return array          Custum columns
     */
    public static function sortListColumns($columns)
    {
        $columns['startDate'] = 'startDate';
        $columns['endDate'] = 'endDate';

        return $columns;
    }

    /**
     * Set list column content
     * @param  string  $column Column id
     * @param  integer $postId Column post id
     * @return void
     */
    public static function listColumnContent($column, $postId)
    {
        switch ($column) {
            case 'startDate':
                echo get_field('startDate', $postId) ? date('Y-m-d \k\l\. H:i', strtotime(get_field('startDate', $postId))) : '';
                break;

            case 'endDate':
                echo get_field('endDate', $postId) ? date('Y-m-d \k\l\. H:i', strtotime(get_field('endDate', $postId))) : '';
                break;
        }
    }

    public static function get($count = 10, $metaQuery = null, $postStatus = array(), $postType = 'event')
    {
        return parent::get($count, $metaQuery, $postStatus, $postType);
    }

    /**
     * Get poi:s
     * @param  integer $count     Number of POI to get
     * @param  array   $metaQuery Optional meta query array
     * @return object             Object with POI posts
     */
    /*public static function get($count = 10, $metaQuery = null, $postStatus = array())
    {
        $args = array(
            'posts_per_page' => $count,
            'post_type'      => 'event',
            'orderby'        => 'date',
            'order'          => 'DESC'
        );

        if ($postStatus) {
            $args['post_status'] = $postStatus;
        }

        if (is_array($metaQuery)) {
            $args['meta_query'] = $metaQuery;
        }

        $posts = get_posts($args);

        if ($count == 1 && isset($posts[0])) {
            $posts = $posts[0];
        }

        return $posts;
    }*/

    /**
     * Adds an event
     *
     * Data should include the following keys
     * - address
     * - ageRestriction
     * - alternateName
     * - bookingLink
     * - bookingPhoneNumber
     * - categories
     * - city
     * - contactEmail
     * - contactPerson
     * - contactPhoneNumber
     * - coOrganizer
     * - country
     * - countryCode
     * - countryCode2
     * - description
     * - doorTime
     * - duration
     * - eventlink
     * - externalLinks
     * - image
     * - ingress
     * - latitude
     * - longitude
     * - municipality
     * - name
     * - occasions
     * - organizerEmail
     * - phoneNumber
     * - postcode
     * - priceAdult
     * - priceChild
     * - priceInformation
     * - publishedDate
     * - status
     * - ticketUrl
     * - url
     * - uniqueId
     * - website
     * @param array $data Event data
     */
    public static function add($data = array())
    {
        $addOne = true;

        $requiredKeysExists = \HbgEventImporter\Helper\Arr::arrayKeysExist(
            $data,
            'address',
            'ageRestriction',
            'alternateName',
            'bookingLink',
            'bookingPhoneNumber',
            'categories',
            'city',
            'contactEmail',
            'contactPerson',
            'contactPhoneNumber',
            'coOrganizer',
            'country',
            'countryCode',
            'countryCode2',
            'description',
            'doorTime',
            'duration',
            'eventlink',
            'externalLinks',
            'image',
            'ingress',
            'latitude',
            'longitude',
            'municipality',
            'name',
            'occasions',
            'organizerEmail',
            'phoneNumber',
            'postcode',
            'priceAdult',
            'priceChild',
            'priceInformation',
            'publishedDate',
            'status',
            'ticketUrl',
            'url',
            'uniqueId',
            'website'
        );

        if (!$requiredKeysExists) {
            return false;
        }

        // Check if event uniqueId already exists

        $postExists = Event::get(1, array(
            array(
                'key' => 'uniqueId',
                'value' => $data['uniqueId'],
                'compare' => '='
            )
        ), array('publish', 'draft', 'pending'), 'event');

        $postId = isset($postExists->ID) ? $postExists->ID : null;

        // Update or create event
        if ($postId !== null) {
            if (get_field('sync', $postId) !== true && get_field('sync', $postId) != '1') {
                return;
            }

            wp_update_post(array(
                'ID'           => $postId,
                'post_title'   => $data['name'],
                'post_content' => $data['description']
            ));
        } else {
            $postId = wp_insert_post(array(
            'post_title'   => $data['name'],
            'post_content' => $data['description'],
            'post_status'  => get_field('xcap_post_status', 'option') ? get_field('xcap_post_status', 'option') : 'publish',
            'post_type'    => 'event',
            'post_date'    =>  $data['publishedDate']
            ));
        }

        $data['categories'] = array_map('trim', $data['categories']);
        $data['categories'] = array_map('ucwords', $data['categories']);

        wp_set_object_terms($postId, $data['categories'], 'event-types', true);

        $data['contactId'] = \HbgEventImporter\Contact::add($data);
        $data['locationId'] = \HbgEventImporter\Location::add($data);
        self::addMeta($postId, $data);
        self::saveImageFromUrl($postId, $data['image']);
    }

    /**
     * Set a event post meta fields
     * @param   integer $postId The post's id
     * @param   object  $data   The xcap data
     * @return  void
     */
    private static function addMeta($postId, $data)
    {
        if (!self::isUrl($data['url'])) {
            $data['url'] = '';
        }

        update_post_meta($postId, 'sync', true);
        update_post_meta($postId, 'uniqueId', $data['uniqueId']);

        //Main
        update_post_meta($postId, 'ingress', $data['ingress']);
        update_post_meta($postId, 'organizerEmail', $data['organizerEmail']);
        update_post_meta($postId, 'phoneNumber', $data['phoneNumber']);
        update_post_meta($postId, 'coOrganizer', $data['coOrganizer']);
        update_post_meta($postId, 'countryCode', $data['countryCode']);
        update_post_meta($postId, 'countryCode2', $data['countryCode2']);
        update_post_meta($postId, 'duration', $data['duration']);
        update_post_meta($postId, 'doorTime', $data['doorTime']);
        update_post_meta($postId, 'eventlink', $data['eventlink']);
        update_post_meta($postId, 'externalLinks', $data['externalLinks']);

        //Booking
        update_post_meta($postId, 'ticketUrl', $data['ticketUrl']);
        //Had to use the key instead of name here
        update_field('field_574451303308d', array(array('link' => $data['bookingLink'])), $postId);
        update_post_meta($postId, 'bookingPhoneNumber', $data['bookingPhoneNumber']);
        update_post_meta($postId, 'priceInformation', $data['priceInformation']);
        update_post_meta($postId, 'ageRestriction', $data['ageRestriction']);
        update_post_meta($postId, 'priceAdult', $data['priceAdult']);
        update_post_meta($postId, 'priceChild', $data['priceChild']);

        //Other
        update_post_meta($postId, 'status', $data['status']);
        update_post_meta($postId, 'alternateName', $data['alternateName']);
        update_post_meta($postId, 'url', $data['url']);
        update_post_meta($postId, 'website', $data['website']);
        update_post_meta($postId, 'categories', $data['categories']);
        update_post_meta($postId, 'longitude', $data['longitude']);

        update_field('field_574d6f51c5204', array($data['contactId']), $postId);
        //update_post_meta($postId, 'contacts', $data['contactId']);

        update_field('field_574e9d12a645e', array($data['locationId']), $postId);
        //update_post_meta($postId, 'location', $data['locationId']);

        //These should instead be created as objects belonging to the event post in some way
        //update_post_meta($postId, 'occasions', $data['occasions']);
    }

    private static function saveImageFromUrl($postId, $url)
    {
        if (!isset($url) || strlen($url) === 0 || !self::isUrl($url)) {
            return false;
        }

        // Upload paths
        $uploadDir = wp_upload_dir();
        $uploadDirUrl = $uploadDir['baseurl'];
        $uploadDir = $uploadDir['basedir'];
        $uploadDir = $uploadDir . '/events';

        if (!is_dir($uploadDir)) {
            if (!mkdir($uploadDir, 0776)) {
                return new WP_Error('event', __('Could not create folder "' . $uploadDir . '", please go ahead and create it manually and rerun the import.'));
            }
        }

        $filename = basename($url);

        // Bail if image already exists in library
        if ($attachmentId = self::attatchmentExists($uploadDir . '/' . basename($filename))) {
            set_post_thumbnail($postId, $attachmentId);
            return;
        }

        // Save file to server
        $contents = file_get_contents($url);
        $save = fopen($uploadDir . '/' . $filename, 'w');
        fwrite($save, $contents);
        fclose($save);

        // Detect filetype
        $filetype = wp_check_filetype($filename, null);

        // Insert the file to media library
        $attachmentId = wp_insert_attachment(array(
            'guid' => $uploadDir . '/' . basename($filename),
            'post_mime_type' => $filetype['type'],
            'post_title' => $filename,
            'post_content' => '',
            'post_status' => 'inherit',
            'post_parent' => $postId
        ), $uploadDir . '/' . $filename, $postId);

        // Generate attachment meta
        require_once(ABSPATH . 'wp-admin/includes/image.php');
        $attachData = wp_generate_attachment_metadata($attachmentId, $uploadDir . '/' . $filename);
        wp_update_attachment_metadata($attachmentId, $attachData);

        set_post_thumbnail($postId, $attachmentId);
    }

    /**
     * Validates url
     * @param  string  $url Url to validate
     * @return boolean
     */
    private static function isUrl($url)
    {
        if (is_string($url) && preg_match('/^(?:[;\/?:@&=+$,]|(?:[^\W_]|[-_.!~*\()\[\] ])|(?:%[\da-fA-F]{2}))*$/', $url)) {
            return true;
        }

        return false;
    }

    /**
     * Checks if a attatchment src already exists in media library
     * @param  string $src Media url
     * @return mixed
     */
    private static function attatchmentExists($src)
    {
        global $wpdb;
        $query = "SELECT ID FROM {$wpdb->posts} WHERE guid = '$src'";
        $id = $wpdb->get_var($query);

        if (!empty($id) && $id > 0) {
            return $id;
        }

        return false;
    }
}
