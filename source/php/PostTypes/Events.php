<?php

namespace HbgEventImporter\PostTypes;

use \HbgEventImporter\Helper\DataCleaner as DataCleaner;

class Events extends \HbgEventImporter\Entity\CustomPostType
{
    public function __construct()
    {
        parent::__construct(
            _x('Events', 'Post type plural', 'event-manager'),
            _x('Event', 'Post type singular', 'event-manager'),
            'event',
            array(
                'description'          =>   'Events with occations and relevant data.',
                'menu_icon'            =>   'data:image/svg+xml;base64,PD94bWwgdmVyc2lvbj0iMS4wIiBlbmNvZGluZz0iaXNvLTg4NTktMSI/Pg0KPCEtLSBHZW5lcmF0b3I6IEFkb2JlIElsbHVzdHJhdG9yIDE2LjAuMCwgU1ZHIEV4cG9ydCBQbHVnLUluIC4gU1ZHIFZlcnNpb246IDYuMDAgQnVpbGQgMCkgIC0tPg0KPCFET0NUWVBFIHN2ZyBQVUJMSUMgIi0vL1czQy8vRFREIFNWRyAxLjEvL0VOIiAiaHR0cDovL3d3dy53My5vcmcvR3JhcGhpY3MvU1ZHLzEuMS9EVEQvc3ZnMTEuZHRkIj4NCjxzdmcgdmVyc2lvbj0iMS4xIiBpZD0iQ2FwYV8xIiB4bWxucz0iaHR0cDovL3d3dy53My5vcmcvMjAwMC9zdmciIHhtbG5zOnhsaW5rPSJodHRwOi8vd3d3LnczLm9yZy8xOTk5L3hsaW5rIiB4PSIwcHgiIHk9IjBweCINCgkgd2lkdGg9Ijk4NS4zMzNweCIgaGVpZ2h0PSI5ODUuMzM0cHgiIHZpZXdCb3g9IjAgMCA5ODUuMzMzIDk4NS4zMzQiIHN0eWxlPSJlbmFibGUtYmFja2dyb3VuZDpuZXcgMCAwIDk4NS4zMzMgOTg1LjMzNDsiDQoJIHhtbDpzcGFjZT0icHJlc2VydmUiPg0KPGc+DQoJPHBhdGggZD0iTTg2OC41NjUsNDkyLjhjLTQuNCwyMi4xMDEtMjQsMzguMi00Ny41LDM5LjJjLTcuNCwwLjMtMTMuNyw1LjctMTUuMTAxLDEzYy0xLjUsNy4zLDIuMiwxNC43LDguOSwxNy44DQoJCWMyMS4zLDEwLDMzLjIsMzIuNCwyOC43LDU0LjVsLTQuMiwyMWMtNS41LDI3LjctMzYuMTAxLDQ1LTYyLjksMzguNGMtNy41LTEuOC0xNS4yLTMuMi0yMi44LTQuN2MtMTEuMi0yLjItMjIuNC00LjUtMzMuNi02LjcNCgkJYy0xNC44MDEtMy0yOS42MDEtNS44OTktNDQuNC04Ljg5OWMtMTcuNi0zLjUtMzUuMy03LjEwMS01Mi45LTEwLjYwMWMtMTkuNjk5LTQtMzkuMzk5LTcuODk5LTU5LjEtMTEuODk5DQoJCWMtMjEtNC4yLTQyLjEtOC40LTYzLjEtMTIuN2MtMjEuNjAxLTQuMy00My4yLTguNy02NC43LTEzYy0yMS40LTQuMy00Mi43LTguNjAxLTY0LjEwMS0xMi45Yy0yMC4zOTktNC4xLTQwLjgtOC4yLTYxLjE5OS0xMi4zDQoJCWMtMTguNy0zLjctMzcuMy03LjUtNTYtMTEuMmMtMTYuMi0zLjItMzIuNC02LjUtNDguNS05LjdjLTEyLjktMi42LTI1LjgtNS4xOTktMzguOC03LjhjLTguOS0xLjgtMTcuODAxLTMuNi0yNi43LTUuMzk5DQoJCWMtNC4xMDEtMC44MDEtOC4yLTEuNy0xMi4zLTIuNWMtMC4yLDAtMC40LTAuMTAxLTAuNjAxLTAuMTAxYzIuMiwxMC40LDEuMiwyMS41LTMuNiwzMS45Yy0xMC4xMDEsMjEuOC0zMy42MDEsMzMuMi01Ni4yLDI4LjgNCgkJYy02LjctMS4zLTE0LDEuMi0xNi45LDcuNGwtOSwxOS41Yy0yLjg5OSw2LjE5OSwwLDEzLjM5OSw1LjMwMSwxNy42OTljMSwwLjgwMSw3MjEuOCwzMzMuMTAxLDcyMi45OTksMzMzLjQNCgkJYzYuNywxLjMsMTQtMS4yLDE2LjktNy40bDktMTkuNWMyLjktNi4xOTksMC0xMy4zOTktNS4zLTE3LjY5OWMtMTgtMTQuMzAxLTI0LjYwMS0zOS42MDEtMTQuNS02MS40YzEwLjEtMjEuOCwzMy42LTMzLjIsNTYuMi0yOC44DQoJCWM2LjY5OSwxLjMsMTQtMS4yLDE2Ljg5OS03LjRsOS0xOS41YzIuOS02LjIsMC0xMy4zOTktNS4zLTE3LjdjLTE4LTE0LjMtMjQuNi0zOS42LTE0LjUtNjEuMzk5czMzLjYtMzMuMiw1Ni4yLTI4LjgNCgkJYzYuNywxLjMsMTQtMS4yLDE2LjktNy40bDktMTkuNWMyLjg5OS02LjIsMC0xMy40LTUuMzAxLTE3LjdjLTE4LTE0LjMtMjQuNi0zOS42LTE0LjUtNjEuNGMxMC4xMDEtMjEuOCwzMy42MDEtMzMuMTk5LDU2LjItMjguOA0KCQljNi43LDEuMywxNC0xLjIsMTYuOS03LjM5OWw5Ljg5OS0yMS42MDFjMi45LTYuMiwwLjItMTMuNS02LTE2LjM5OWwtMTA3LjY5OS00OS43TDg2OC41NjUsNDkyLjh6Ii8+DQoJPHBhdGggZD0iTTkuNjY1LDQ4NS45YzEuMiwwLjYsNzc5LjMsMTU2LjY5OSw3ODAuNiwxNTYuNjk5YzYuODAxLTAuMywxMy40LTQuNSwxNC43LTExLjFsNC4yLTIxYzEuMy02LjctMy4xLTEzLjEtOS4zLTE2DQoJCWMtMjAuOC05LjgtMzMuMTAxLTMyLjgtMjguNC01Ni40YzQuNy0yMy42LDI1LTQwLjEsNDgtNDEuMWM2LjgtMC4zLDEzLjQtNC41LDE0LjctMTEuMWwzLjEtMTUuNGwxLjEwMS01LjcNCgkJYzEuMy02LjctMy4xMDEtMTMuMS05LjMtMTZjLTIwLjgwMS05LjgtMzMuMTAxLTMyLjgtMjguNC01Ni4zOTljNC43LTIzLjYwMSwyNS00MC4xMDEsNDgtNDEuMTAxYzYuOC0wLjMsMTMuNC00LjUsMTQuNy0xMS4xDQoJCWw0LjItMjFjMS4zLTYuNy0zLjEwMS0xMy4xLTkuMzAxLTE2Yy0yMC44LTkuOC0zMy4xLTMyLjgtMjguMzk5LTU2LjRjNC43LTIzLjYsMjUtNDAuMSw0OC00MS4xYzYuOC0wLjMsMTMuMzk5LTQuNSwxNC43LTExLjENCgkJbDQuNjk5LTIzLjNjMS4zMDEtNi43LTMtMTMuMi05LjY5OS0xNC41YzAsMC03ODEuOS0xNTYuOC03ODIuNy0xNTYuOGMtNS44LDAtMTAuOSw0LjEtMTIuMSw5LjlsLTQuNywyMy4zDQoJCWMtMS4zLDYuNywzLjEsMTMuMSw5LjMsMTZjMjAuOCw5LjgsMzMuMSwzMi44LDI4LjQsNTYuNGMtNC43LDIzLjYtMjUsNDAuMS00OCw0MS4xYy02LjgwMSwwLjMtMTMuNCw0LjUtMTQuNywxMS4xbC00LjIsMjENCgkJYy0xLjMsNi43LDMuMSwxMy4xLDkuMywxNmMyMC44LDkuOCwzMy4xMDEsMzIuOCwyOC40LDU2LjRjLTQuNywyMy42LTI1LDQwLjEtNDgsNDEuMWMtNi44LDAuMy0xMy40LDQuNS0xNC43LDExLjFsLTQuMiwyMQ0KCQljLTEuMyw2LjcsMy4xMDEsMTMuMSw5LjMsMTZjMjAuODAxLDkuOCwzMy4xMDEsMzIuOCwyOC40LDU2LjRjLTQuNywyMy42MDEtMjUsNDAuMTAxLTQ4LDQxLjEwMWMtNi44LDAuMy0xMy40LDQuNS0xNC43LDExLjENCgkJbC00LjIsMjFDLTAuOTM1LDQ3Ni43LDMuNDY0LDQ4Myw5LjY2NSw0ODUuOXogTTY3Ni4xNjUsMjI5LjZjMi43LTEzLjUsMTUuOS0yMi4zLDI5LjQtMTkuNnMyMi4zLDE1LjksMTkuNiwyOS40bC0zMywxNjQuMg0KCQlsLTIwLjMsMTAxLjJjLTIuNCwxMS45LTEyLjgsMjAuMTAxLTI0LjUsMjAuMTAxYy0xLjYwMSwwLTMuMy0wLjItNC45LTAuNWMtMTMuNS0yLjctMjIuMy0xNS45LTE5LjYtMjkuNGwyMi43LTExMi45TDY3Ni4xNjUsMjI5LjYNCgkJeiBNMjI1LjM2NSwxMzkuMWMyLjctMTMuNSwxNS45LTIyLjMsMjkuNC0xOS42czIyLjMsMTUuOSwxOS42LDI5LjRsLTExLjQsNTYuN2wtMTIuODk5LDY0LjNsLTEwLjQsNTEuOGwtMTguNSw5Mi42DQoJCWMtMi4zOTksMTEuOS0xMi44LDIwLjEwMS0yNC41LDIwLjEwMWMtMS42LDAtMy4zLTAuMi00Ljg5OS0wLjVjLTAuNy0wLjEwMS0xLjQtMC4zMDEtMi0wLjVjLTEyLjQtMy42MDEtMjAuMTAxLTE2LjEwMS0xNy41LTI4LjkNCgkJbDMuNjk5LTE4LjdsOS43LTQ4LjRMMjI1LjM2NSwxMzkuMXoiLz4NCjwvZz4NCjxnPg0KPC9nPg0KPGc+DQo8L2c+DQo8Zz4NCjwvZz4NCjxnPg0KPC9nPg0KPGc+DQo8L2c+DQo8Zz4NCjwvZz4NCjxnPg0KPC9nPg0KPGc+DQo8L2c+DQo8Zz4NCjwvZz4NCjxnPg0KPC9nPg0KPGc+DQo8L2c+DQo8Zz4NCjwvZz4NCjxnPg0KPC9nPg0KPGc+DQo8L2c+DQo8Zz4NCjwvZz4NCjwvc3ZnPg0K',
                'public'               =>   true,
                'publicly_queriable'   =>   true,
                'show_ui'              =>   true,
                'show_in_nav_menus'    =>   true,
                'has_archive'          =>   true,
                'rewrite'              =>   array(
                    'slug'       =>   'event',
                    'with_front' =>   false
                ),
                'hierarchical'          =>  false,
                'exclude_from_search'   =>  false,
                'taxonomies'            =>  array('event_categories', 'event_tags'),
                'supports'              =>  array('title', 'revisions', 'editor', 'thumbnail'),
                'map_meta_cap'          =>  true,
                'capability_type'       =>  'event'
            )
        );

        $this->addTableColumn('cb', '<input type="checkbox">');

        $this->addTableColumn('title', __('Title', 'event-manager'));

        $this->addTableColumn('location', __('Location', 'event-manager'), true, function ($column, $postId) {
            $locationId = get_field('location', $postId);

            if (!$locationId) {
                return;
            }

            echo '<a href="' . get_edit_post_link($locationId) . '">' . get_the_title($locationId) . '</a>';
        });

        $this->addTableColumn('organizer', _x('Main organizer', 'Main organizer column name', 'event-manager'), true, function ($column, $postId) {
            $value = null;

            if (have_rows('organizers')) {
                while (have_rows('organizers')) {
                    the_row();
                    if (get_sub_field('main_organizer')) {
                        if (! empty(get_sub_field('organizer'))) {
                            $value = get_the_title(get_sub_field('organizer'));
                        }
                    }
                }
            }

            if (!$value) {
                return;
            }

            echo($value);
        });

        $this->addTableColumn('import_client', __('Client', 'event-manager'), true, function ($column, $postId) {
            $import     = get_post_meta($postId, 'import_client', true);
            $consumer   = get_post_meta($postId, 'consumer_client', true);

            if (!empty($import)) {
                echo ucwords($import);
            } elseif (! empty($consumer)) {
                echo $consumer;
            } else {
                return;
            }
        });

        $this->addTableColumn('date', __('Date', 'event-manager'));

        // Disable auto embedded urls
        remove_filter('the_content', array($GLOBALS['wp_embed'], 'autoembed'), 8);

        add_action('save_post', array($this, 'saveEventOccasions'), 10, 3);
        add_action('save_post', array($this, 'saveRecurringEvents'), 11, 3);
        add_action('save_post', array($this, 'extractEventTags'), 10, 3);

        add_action('delete_post', array($this, 'deleteEventOccasions'), 10);

        add_action('admin_notices', array($this, 'duplicateNotice'));
        add_action('admin_notices', array($this, 'importCbisWarning'));
        add_action('admin_notices', array($this, 'importXcapWarning'));
        add_action('admin_notices', array($this, 'importTransticketWarning'));
        add_action('admin_notices', array($this, 'eventInstructions'));

        add_action('admin_action_duplicate_post', array($this, 'duplicatePost'));
        add_action('admin_head-edit.php', array($this, 'adminHeadAction'));

        add_filter('views_edit-event', array($this, 'addImportButtons'));
        add_filter('the_content', array($this, 'replaceWhitespace'), 9);
        add_filter('post_row_actions', array($this, 'duplicatePostLink'), 10, 2);

        add_filter('acf/validate_value/name=end_date', array($this, 'validateEndDate'), 10, 4);
        add_filter('acf/validate_value/name=door_time', array($this, 'validateDoorTime'), 10, 4);
        add_filter('acf/validate_value/name=occasions', array($this, 'validateOccasion'), 10, 4);
        add_filter('acf/validate_value/name=rcr_rules', array($this, 'validateOccasion'), 10, 4);
        add_filter('acf/validate_value/name=rcr_door_time', array($this, 'validateRcrDoorTime'), 10, 4);
        add_filter('acf/validate_value/name=rcr_end_date', array($this, 'validateRcrEndDate'), 10, 4);
        add_filter('acf/validate_value/name=price_adult', array($this, 'validatePrice'), 10, 4);
        add_filter('acf/validate_value/name=price_children', array($this, 'validatePrice'), 10, 4);
        add_filter('acf/validate_value/name=price_student', array($this, 'validatePrice'), 10, 4);
        add_filter('acf/validate_value/name=price_senior', array($this, 'validatePrice'), 10, 4);
        add_filter('acf/validate_value/name=price_group', array($this, 'validatePrice'), 10, 4);
        add_filter('acf/validate_value/name=seated_minimum_price', array($this, 'validatePrice'), 10, 4);
        add_filter('acf/validate_value/name=seated_maximum_price', array($this, 'validatePrice'), 10, 4);
        add_filter('acf/validate_value/name=standing_minimum_price', array($this, 'validatePrice'), 10, 4);
        add_filter('acf/validate_value/name=standing_maximum_price', array($this, 'validatePrice'), 10, 4);
        add_filter('acf/validate_value/name=maximum_price', array($this, 'validatePrice'), 10, 4);
        add_filter('acf/validate_value/name=minimum_price', array($this, 'validatePrice'), 10, 4);

        add_filter('acf/update_value/name=maximum_price', array($this, 'acfUpdatePrices'), 10, 4);
        add_filter('acf/update_value/name=minimum_price', array($this, 'acfUpdatePrices'), 10, 4);
        add_filter('acf/update_value/name=seated_minimum_price', array($this, 'acfUpdatePrices'), 10, 4);
        add_filter('acf/update_value/name=seated_maximum_price', array($this, 'acfUpdatePrices'), 10, 4);
        add_filter('acf/update_value/name=standing_minimum_price', array($this, 'acfUpdatePrices'), 10, 4);
        add_filter('acf/update_value/name=standing_maximum_price', array($this, 'acfUpdatePrices'), 10, 4);
        add_filter('acf/update_value/name=price_adult', array($this, 'acfUpdatePrices'), 10, 3);
        add_filter('acf/update_value/name=price_children', array($this, 'acfUpdatePrices'), 10, 3);
        add_filter('acf/update_value/name=price_student', array($this, 'acfUpdatePrices'), 10, 3);
        add_filter('acf/update_value/name=price_senior', array($this, 'acfUpdatePrices'), 10, 3);
        add_filter('acf/update_value/key=field_57f4f6dc747a1', array($this, 'acfUpdatePrices'), 10, 3);
        add_filter('acf/update_value/name=booking_phone', array($this, 'acfUpdatePhone'), 10, 3);
        add_filter('acf/update_value/key=field_57ebb45142846', array($this, 'acfUpdatePhone'), 10, 3);
        add_filter('acf/update_value/key=field_5b03c6f3cd820', array($this, 'acfUpdatePhone'), 10, 3);

        add_filter('acf/fields/post_object/result/name=location', array($this, 'acfLocationSelect'), 10, 4);
        add_filter('acf/fields/post_object/result/name=additional_locations', array($this, 'acfLocationSelect'), 10, 4);
        add_filter('acf/fields/post_object/query', array($this, 'acfPostObjectStatus'), 10, 3);

        add_filter('manage_edit-' . $this->slug . '_columns', array($this, 'addAcceptDenyTable'));
        add_action('manage_' . $this->slug . '_posts_custom_column', array($this, 'addAcceptDenyButtons'), 10, 2);
    }

    /**
     * Save event_occasions table when an event is published or updated.
     * @param  int  $post_id event post id
     * @param  post $post The post object.
     * @param  bool $update Whether this is an existing post being updated or not.
     */
    public function saveEventOccasions($post_id, $post, $update)
    {
        if ($this->slug != $post->post_type || !$update) {
            return;
        }

        global $wpdb;
        $dbTable = $wpdb->prefix . "occasions";
        $wpdb->delete($dbTable, array('event' => $post_id ), array('%d'));

        // Get occasions
        $occasions = get_field('occasions', $post_id);
        if (!is_array($occasions) || empty($occasions)) {
            return;
        }

        foreach ($occasions as $occasion) {
            $timestampStart = strtotime($occasion['start_date']);
            $timestampEnd = strtotime($occasion['end_date']);
            $timestampDoor = !is_null($occasion['door_time']) ? strtotime($occasion['door_time']) : null;

            $wpdb->insert(
                $dbTable,
                array(
                    'event' => $post_id,
                    'timestamp_start' => $timestampStart,
                    'timestamp_end' => $timestampEnd,
                    'timestamp_door' => $timestampDoor
                )
            );
        }
    }

    /**
     * Save recurring events to event_occasions table.
     * @param  int  $post_id event post id
     * @param  post $post The post object.
     * @param  bool $update Whether this is an existing post being updated or not.
     */
    public function saveRecurringEvents($post_id, $post, $update)
    {
        if ($this->slug != $post->post_type || !$update) {
            return;
        }

        $rules = get_field('rcr_rules', $post_id);

        if (!is_array($rules)) {
            return;
        }

        global $wpdb;
        $dbTable = $wpdb->prefix . "occasions";

        foreach ($rules as $rule) {
            $startDate = $rule['rcr_start_date'];
            $endDate = $rule['rcr_end_date'];
            $weekday = $rule['rcr_week_day'];
            $startTime = $rule['rcr_start_time'];
            $endTime = $rule['rcr_end_time'];
            $doorTime = $rule['rcr_door_time'];
            $weekInterval = $rule['rcr_weekly_interval'];

            // Get recurring dates
            $recurringDates = array();
            for ($j = strtotime($weekday, strtotime($startDate)); $j <= strtotime($endDate); $j = strtotime('+' . $weekInterval . ' week', $j)) {
                $recurringDates[] = $j;
            }

            // Remove exceptions from recurring dates
            $exceptionDates = $rule['rcr_exceptions'];
            if ($exceptionDates) {
                foreach ($exceptionDates as $key => &$date) {
                    // Keep occasions with status cancelled or rescheduled
                    if ($date['status_rcr_exc'] == 'default') {
                        $date = strtotime($date['rcr_exc_date']);
                    } else {
                        unset($exceptionDates[$key]);
                    }
                }
                // Filter exceptions from the recurring dates
                $recurringDates = array_diff($recurringDates, $exceptionDates);
            }

            foreach ($recurringDates as $date) {
                $start = strtotime(date('Y-m-d', $date) . ' ' . $startTime);
                $end = strtotime(date('Y-m-d', $date) . ' ' . $endTime);
                // If end time is before start time, add 1 day
                if ($start >= $end) {
                    $end = strtotime('+1 day', $end);
                }
                $door = null;

                if (!empty($doorTime)) {
                    $door = strtotime(date('Y-m-d', $date) . ' ' . $doorTime);
                }

                $wpdb->insert(
                    $dbTable,
                    array(
                        'event' => $post_id,
                        'timestamp_start' => $start,
                        'timestamp_end' => $end,
                        'timestamp_door' => $door
                    )
                );
            }
        }
    }

    /**
     * Delete event_occasions when an event is permanently deleted.
     * @param  int $post_id event post id
     */
    public function deleteEventOccasions($post_id)
    {
        global $wpdb;
        $db_occasions = $wpdb->prefix . "occasions";

        if ($wpdb->get_var($wpdb->prepare("SELECT event FROM $db_occasions WHERE event = %d", $post_id))) {
            $wpdb->query($wpdb->prepare("DELETE FROM $db_occasions WHERE event = %d", $post_id));
        }
    }

    /**
     * Validate end date to be 'greater' than start date.
     * @param  mixed  $valid Whether or not the value is valid (true / false).
     * @param  mixed  $value The value to be saved
     * @param  array  $field An array containing all the field settings for the field which was used to upload the attachment
     * @param  string $input the DOM element’s name attribute
     * @return mixed  $valid Return if true or false
     */
    public function validateEndDate($valid, $value, $field, $input)
    {
        if (!$valid) {
            return $valid;
        }

        $repeater_key = 'field_5761106783967';
        $start_key = 'field_5761109a83968';
        // $end_key = 'field_576110e583969';

        $row = preg_replace('/^\s*acf\[[^\]]+\]\[([^\]]+)\].*$/', '\1', $input);
        $start_value = $_POST['acf'][$repeater_key][$row][$start_key];
        $end_value = $value;

        if (strtotime($end_value) <= strtotime($start_value)) {
            $valid = __('End date must be after start date', 'event-manager');
        }

        return $valid;
    }

    /**
     * Validate door time to be equal or greater than start date.
     * @param  mixed  $valid Whether or not the value is valid (true / false).
     * @param  mixed  $value The value to be saved
     * @param  array  $field An array containing all the field settings for the field which was used to upload the attachment
     * @param  string $input the DOM element’s name attribute
     * @return mixed  $valid Return if true or false
     */
    public function validateDoorTime($valid, $value, $field, $input)
    {
        if (!$valid) {
            return $valid;
        }

        $repeater_key = 'field_5761106783967';
        $start_key = 'field_5761109a83968';

        $row = preg_replace('/^\s*acf\[[^\]]+\]\[([^\]]+)\].*$/', '\1', $input);
        $start_value = $_POST['acf'][$repeater_key][$row][$start_key];
        $door_value = $value;

        if (strtotime($door_value) > strtotime($start_value)) {
            $valid = __('Door time cannot be after start date', 'event-manager');
        }

        return $valid;
    }

    /**
     * Check if occasion och recurrence rules are set.
     * @param  mixed  $valid Whether or not the value is valid (true / false).
     * @param  mixed  $value The value to be saved
     * @param  array  $field An array containing all the field settings for the field which was used to upload the attachment
     * @param  string $input the DOM element’s name attribute
     * @return mixed  $valid Return if true or false
     */
    public function validateOccasion($valid, $value, $field, $input)
    {
        if (!$valid) {
            return $valid;
        }

        $occasions = $_POST['acf']['field_5761106783967'];
        $rcr_rules = $_POST['acf']['field_57d2749e3bf4d'];

        if (empty($occasions) && empty($rcr_rules)) {
            $valid = __('Please add occasion or recurrence rule', 'event-manager');
        }

        return $valid;
    }

    /**
     * Validate price to be numeric
     * @param  mixed  $valid Whether or not the value is valid (true / false).
     * @param  mixed  $value The value to be saved
     * @param  array  $field An array containing all the field settings for the field which was used to upload the attachment
     * @param  string $input the DOM element’s name attribute
     * @return mixed  $valid Return if true or false
     */
    public function validatePrice($valid, $value, $field, $input)
    {
        if (!$valid || empty($value)) {
            return $valid;
        }

        $value1 = str_replace(',', '.', $value);
        $value2 = str_replace(' ', '', $value1);

        if (!is_numeric($value2)) {
            $valid = __('Not a valid number', 'event-manager');
        }

        return $valid;
    }

    /**
     * Validate recurring rules "end time" to be 'greater' than start time.
     * @param  mixed  $valid Whether or not the value is valid (true / false).
     * @param  mixed  $value The value to be saved
     * @param  array  $field An array containing all the field settings for the field which was used to upload the attachment
     * @param  string $input the DOM element’s name attribute
     * @return mixed  $valid Return if true or false
     */
    public function validateRcrEndTime($valid, $value, $field, $input)
    {
        if (!$valid) {
            return $valid;
        }

        $repeater_key = 'field_57d2749e3bf4d';
        $start_key = 'field_57d277153bf4f';
        $row = preg_replace('/^\s*acf\[[^\]]+\]\[([^\]]+)\].*$/', '\1', $input);
        $start_value = $_POST['acf'][$repeater_key][$row][$start_key];
        $end_value = $value;

        if ($end_value <= $start_value) {
            $valid = __('End time must be after start time', 'event-manager');
        }

        return $valid;
    }

    /**
     * Validate recurring rules "door time" to be 'greater' than start time.
     * @param  mixed  $valid Whether or not the value is valid (true / false).
     * @param  mixed  $value The value to be saved
     * @param  array  $field An array containing all the field settings for the field which was used to upload the attachment
     * @param  string $input the DOM element’s name attribute
     * @return mixed  $valid Return if true or false
     */
    public function validateRcrDoorTime($valid, $value, $field, $input)
    {
        if (!$valid) {
            return $valid;
        }

        $repeater_key = 'field_57d2749e3bf4d';
        $start_key = 'field_57d277153bf4f';
        $row = preg_replace('/^\s*acf\[[^\]]+\]\[([^\]]+)\].*$/', '\1', $input);
        $start_value = $_POST['acf'][$repeater_key][$row][$start_key];
        $door_value = $value;

        if ($door_value > $start_value) {
            $valid = __('Door time cannot be after start time', 'event-manager');
        }

        return $valid;
    }

    /**
     * Validate recurring rules interval "end date" to be 'greater' than start date.
     * @param  mixed  $valid Whether or not the value is valid (true / false).
     * @param  mixed  $value The value to be saved
     * @param  array  $field An array containing all the field settings for the field which was used to upload the attachment
     * @param  string $input the DOM element’s name attribute
     * @return mixed  $valid Return if true or false
     */
    public function validateRcrEndDate($valid, $value, $field, $input)
    {
        if (!$valid) {
            return $valid;
        }
        $repeater_key = 'field_57d2749e3bf4d';
        $start_key = 'field_57d660a687234';
        $row = preg_replace('/^\s*acf\[[^\]]+\]\[([^\]]+)\].*$/', '\1', $input);
        $start_value = $_POST['acf'][$repeater_key][$row][$start_key];
        $end_value = $value;

        if (strtotime($end_value) <= strtotime($start_value)) {
            $valid = __('End date must be after start date', 'event-manager');
        }

        return $valid;
    }

    /**
     * Sanitize prices before save to dabtabase
     * @param  string $value   the value of the field
     * @param  int    $post_id the post id to save against
     * @param  array  $field   the field object
     * @return string          the new value
     */
    public function acfUpdatePrices($value, $post_id, $field)
    {
        $value = DataCleaner::price($value);
        return $value;
    }

    /**
     * Add buttons to start parsing xcap and Cbis
     * @return void
     */
    public function addImportButtons($views)
    {
        if (current_user_can('administrator')) {
            $button = '<div class="import-buttons actions">';

            if (have_rows('xcap_api_urls', 'option')) {
                $button .= '<button class="button-primary extraspace single-import" data-client="xcap">' . __('Import XCAP', 'event-manager') . '</button>';
            }

            if (have_rows('cbis_api_keys', 'option')) {
                $button .= '<button class="button-primary extraspace single-import" data-client="cbis">' . __('Import CBIS', 'event-manager') . '</button>';
            }

            if (have_rows('transticket_api_urls', 'option')) {
                $button .= '<button class="button-primary extraspace single-import" data-client="transticket">' . __('Import Transticket', 'event-manager') . '</button>';
            }

            if (have_rows('ols_api_urls', 'option')) {
                $button .= '<button class="button-primary extraspace single-import" data-client="ols">' . __('Import Open Library', 'event-manager') . '</button>';
            }

            $button .= '</div>';
            $views['import-buttons'] = $button;
        }

        return $views;
    }

    /**
     * Clone event as a draft and redirects to edit post
     * @return void
     */
    public function duplicatePost()
    {
        global $wpdb;

        if (!(isset($_GET['post']) || isset($_POST['post'])  || (isset($_REQUEST['action']) && 'duplicate_post' == $_REQUEST['action']))) {
            wp_die(__('No post to duplicate has been supplied!', 'event-manager'));
        }

        $post_id = (isset($_GET['post']) ? absint($_GET['post']) : absint($_POST['post']));
        $post = get_post($post_id);

        $current_user = wp_get_current_user();
        $new_post_author = $current_user->ID;

        if (!isset($post) || empty($post)) {
            wp_die(__('Event creation failed, could not find original event', 'event-manager').': ' . $post_id);
            return;
        }

        $args = array(
            'comment_status' => $post->comment_status,
            'ping_status'    => $post->ping_status,
            'post_author'    => $new_post_author,
            'post_content'   => $post->post_content,
            'post_excerpt'   => $post->post_excerpt,
            'post_name'      => '',
            'post_parent'    => $post->post_parent,
            'post_password'  => $post->post_password,
            'post_status'    => 'draft',
            'post_title'     => '',
            'post_type'      => $post->post_type,
            'to_ping'        => $post->to_ping,
            'menu_order'     => $post->menu_order
        );

        $new_post_id = wp_insert_post($args);

        // get current post terms and set them to the new event draft
        $taxonomies = get_object_taxonomies($post->post_type);
        foreach ($taxonomies as $taxonomy) {
            $post_terms = wp_get_object_terms($post_id, $taxonomy, array('fields' => 'slugs'));
            wp_set_object_terms($new_post_id, $post_terms, $taxonomy, false);
        }

        // duplicate all post meta
        $post_meta_infos = $wpdb->get_results("SELECT meta_key, meta_value FROM $wpdb->postmeta WHERE post_id=$post_id");

        if (count($post_meta_infos) != 0) {
            $sql_query = "INSERT INTO $wpdb->postmeta (post_id, meta_key, meta_value) ";
            // Filter certain values from imported events
            foreach ($post_meta_infos as $meta_info) {
                $meta_key = $meta_info->meta_key;
                switch ($meta_key) {
                    case 'import_client':
                        continue 2;

                    case 'imported_post':
                        $meta_value = addslashes(0);
                        break;

                    case 'sync':
                        $meta_value = addslashes(0);
                        break;

                    default:
                        $meta_value = addslashes($meta_info->meta_value);
                        break;
                }

                $sql_query_sel[]= "SELECT $new_post_id, '$meta_key', '$meta_value'";
            }

            $sql_query.= implode(" UNION ALL ", $sql_query_sel);
            $wpdb->query($sql_query);
        }

        wp_redirect(admin_url('post.php?action=edit&post=' . $new_post_id.'&duplicate=' . $post_id));
        exit;
    }

    /**
     * Adds "clone" link to events.
     * @param  array   $actions An array of row action links
     * @param  WP_Post $post    The post object.
     * @return array
     */
    public function duplicatePostLink($actions, $post)
    {
        if ($post->post_type == 'event' && current_user_can('edit_events')) {
            $actions['duplicate'] = '<a href="admin.php?action=duplicate_post&amp;post=' . $post->ID . '" title="'.__('Create similar item', 'event-manager').'" rel="permalink" onclick="return confirm(\''.__('Are you sure you want to clone this event?', 'event-manager').'\');">'.__('Clone', 'event-manager').'</a>';
        }

        return $actions;
    }

    /**
     * Show admin notification after cloning an event.
     * @return void
     */
    public function duplicateNotice()
    {
        if (!isset($_GET['duplicate'])) {
            return;
        }

        $id = $_GET['duplicate'];
        $msg = sprintf(__('This is a duplicate of the event: %s. If this is a new occasion for the same event, please %s and republish the original event', 'event-manager'), '<strong>"' . get_the_title($id) . '"</strong>', '<a href="' . esc_url(get_edit_post_link($id))  .'">'.__('edit', 'event-manager').'</a>');

        echo '<div class="notice notice-warning is-dismissible">';
        echo '<p>' . $msg . '</p>';
        echo '</div>';
    }

    /**
     * Show initial instructions as a dismissable notification.
     * @return void
     */
    public function eventInstructions()
    {
        $screen = get_current_screen();
        $userId = get_current_user_id();

        if ($screen->post_type !== 'event' || $screen->base !== 'post' || get_user_meta($userId, 'dismissed_instr', true) == 1) {
            return;
        }

        printf('<div class="%s"><h3>%s</h3><p><strong>%s</strong></p><p>%s</p><h4>%s:</h4><ul><li>– %s</li><li>– %s</li><li>– %s</li></ul></div>',
            esc_attr('notice notice-info dismissable is-dismissible event-guidelines'),
            esc_html(__('Guidelines', 'event-manager')),
            esc_html(__('Please read the guidelines below before publishing your event.', 'event-manager')),
            esc_html(__('Do not enter information that refers to information in another text field. It is not certain that all fields will be presented to the consumer and would thefore be missguiding.', 'event-manager')),
            esc_html(__('Plain language tips', 'event-manager')),
            esc_html(__('Write the most important first.', 'event-manager')),
            esc_html(__('Use words that you think the readers understand.', 'event-manager')),
            esc_html(__('Write short and concise.', 'event-manager'))
        );
    }

    /**
     * Show warning if CBIS haven't imported any events the last 7 days.
     * @return void
     */
    public function importCbisWarning()
    {
        $screen = get_current_screen();
        $filter = (isset($_GET['filter_action'])) ? $_GET['filter_action'] : false;

        $optionsChecked = (get_field('import_warning', 'option') == true && get_field('cbis_daily_cron', 'option') == true) ? true : false;
        if ($screen->post_type != 'event' || $optionsChecked != true || $filter || ! current_user_can('administrator')) {
            return;
        }

        $latestPost = get_posts("post_type=event&numberposts=1&meta_key=import_client&meta_value=cbis&post_status=any");
        if (empty($latestPost[0]) || strtotime($latestPost[0]->post_date) > strtotime('-1 week')) {
            return;
        }

        echo '<div class="notice notice-warning is-dismissible"><p>';
        _e('CBIS have not imported any events for atleast 7 days. Please control the importer.', 'event-manager');
        echo '</p></div>';
    }

    /**
     * Show warning if XCAP haven't imported any events the last 7 days.
     * @return void
     */
    public function importXcapWarning()
    {
        $screen = get_current_screen();
        $filter = (isset($_GET['filter_action'])) ? $_GET['filter_action'] : false;

        $optionsChecked = (get_field('import_warning', 'option') == true && get_field('xcap_daily_cron', 'option') == true) ? true : false;
        if ($screen->post_type != 'event' || $optionsChecked != true || $filter || ! current_user_can('administrator')) {
            return;
        }

        $latestPost = get_posts("post_type=event&numberposts=1&meta_key=import_client&meta_value=xcap&post_status=any");
        if (empty($latestPost[0]) || strtotime($latestPost[0]->post_date) > strtotime('-1 week')) {
            return;
        }

        echo '<div class="notice notice-warning is-dismissible"><p>';
        _e('XCAP have not imported any events for atleast 7 days. Please control the importer.', 'event-manager');
        echo '</p></div>';
    }

    /**
     * Show warning if Transticket haven't imported any events the last 7 days.
     * @return void
     */
    public function importTransticketWarning()
    {
        $screen = get_current_screen();
        $filter = (isset($_GET['filter_action'])) ? $_GET['filter_action'] : false;

        $optionsChecked = (get_field('import_warning', 'option') == true && get_field('transticket_daily_cron', 'option') == true) ? true : false;
        if ($screen->post_type != 'event' || $optionsChecked != true || $filter || ! current_user_can('administrator')) {
            return;
        }

        $latestPost = get_posts("post_type=event&numberposts=1&meta_key=import_client&meta_value=transticket&post_status=any");
        if (empty($latestPost[0]) || strtotime($latestPost[0]->post_date) > strtotime('-1 week')) {
            return;
        }

        echo '<div class="notice notice-warning is-dismissible"><p>';
        _e('Transticket have not imported any events for atleast 7 days. Please control the importer.', 'event-manager');
        echo '</p></div>';
    }


    /**
     * Saves hashtags from content as event_tags
     * @return void
     */
    public function extractEventTags($post_id)
    {
        DataCleaner::hashtags($post_id, 'event_tags');
    }

    /**
     * Get only published post objects
     * @param  array $args    the WP_Query args used to find choices
     * @param  array $field   the field array containing all attributes & settings
     * @param  int   $post_id the current post ID being edited
     * @return array          updated WP_Query args
     */
    public function acfPostObjectStatus($args, $field, $post_id)
    {
        $args['post_status'] = 'publish';
        return $args;
    }

    /**
     * Adding address to Location select box
     * @param  string   $title    the text displayed for this post object
     * @param  object   $post     the post object
     * @param  array    $field    the field array containing all attributes & settings
     * @param  int      $post_id  the current post ID being edited
     * @return string             updated title
     */
    public function acfLocationSelect($title, $post, $field, $post_id)
    {
        $address = get_post_meta($post->ID, 'formatted_address', true);

        if (! empty($address)) {
            $title .= ' (' . $address .  ')';
        }

        return $title;
    }

    /**
     * Replace unwanted whitespaces coming from different text editors
     * @param  string $content Defualt content
     * @return string          Modified content string
     */
    public function replaceWhitespace($content)
    {
        $string  = htmlentities($content, null, 'utf-8');
        $content = str_replace('&nbsp;', ' ', $string);
        $content = html_entity_decode($content);

        return $content;
    }

    /**
     * Init post state filter
     */
    public function adminHeadAction() {
        add_filter('display_post_states', array($this, 'setPostState'), 10, 2);
    }

    /**
     * Add post state to 'Under processing'
     * @param array $postStates Default states
     * @param object $post Post object
     * @return array $postStates Modified states
     */
    public function setPostState($postStates, $post) {
        $underProcessing = get_field('event_under_processing', $post->ID);
        if ($post->post_type == $this->slug && $underProcessing && $post->post_status != 'publish') {
            $postStates[$post->post_status] = __('Under processing', 'event-manager');
        }
        return $postStates;
    }
}
