<?php

namespace HbgEventImporter\PostTypes;

class Events extends \HbgEventImporter\Entity\CustomPostType
{
    public function __construct()
    {
        parent::__construct(
            __('Events', 'event-manager'),
            __('Event', 'event-manager'),
            'event',
            array(
                'description'          =>   'Events',
                'menu_icon'            =>   'data:image/svg+xml;base64,PD94bWwgdmVyc2lvbj0iMS4wIiBlbmNvZGluZz0iaXNvLTg4NTktMSI/Pg0KPCEtLSBHZW5lcmF0b3I6IEFkb2JlIElsbHVzdHJhdG9yIDE2LjAuMCwgU1ZHIEV4cG9ydCBQbHVnLUluIC4gU1ZHIFZlcnNpb246IDYuMDAgQnVpbGQgMCkgIC0tPg0KPCFET0NUWVBFIHN2ZyBQVUJMSUMgIi0vL1czQy8vRFREIFNWRyAxLjEvL0VOIiAiaHR0cDovL3d3dy53My5vcmcvR3JhcGhpY3MvU1ZHLzEuMS9EVEQvc3ZnMTEuZHRkIj4NCjxzdmcgdmVyc2lvbj0iMS4xIiBpZD0iQ2FwYV8xIiB4bWxucz0iaHR0cDovL3d3dy53My5vcmcvMjAwMC9zdmciIHhtbG5zOnhsaW5rPSJodHRwOi8vd3d3LnczLm9yZy8xOTk5L3hsaW5rIiB4PSIwcHgiIHk9IjBweCINCgkgd2lkdGg9Ijk4NS4zMzNweCIgaGVpZ2h0PSI5ODUuMzM0cHgiIHZpZXdCb3g9IjAgMCA5ODUuMzMzIDk4NS4zMzQiIHN0eWxlPSJlbmFibGUtYmFja2dyb3VuZDpuZXcgMCAwIDk4NS4zMzMgOTg1LjMzNDsiDQoJIHhtbDpzcGFjZT0icHJlc2VydmUiPg0KPGc+DQoJPHBhdGggZD0iTTg2OC41NjUsNDkyLjhjLTQuNCwyMi4xMDEtMjQsMzguMi00Ny41LDM5LjJjLTcuNCwwLjMtMTMuNyw1LjctMTUuMTAxLDEzYy0xLjUsNy4zLDIuMiwxNC43LDguOSwxNy44DQoJCWMyMS4zLDEwLDMzLjIsMzIuNCwyOC43LDU0LjVsLTQuMiwyMWMtNS41LDI3LjctMzYuMTAxLDQ1LTYyLjksMzguNGMtNy41LTEuOC0xNS4yLTMuMi0yMi44LTQuN2MtMTEuMi0yLjItMjIuNC00LjUtMzMuNi02LjcNCgkJYy0xNC44MDEtMy0yOS42MDEtNS44OTktNDQuNC04Ljg5OWMtMTcuNi0zLjUtMzUuMy03LjEwMS01Mi45LTEwLjYwMWMtMTkuNjk5LTQtMzkuMzk5LTcuODk5LTU5LjEtMTEuODk5DQoJCWMtMjEtNC4yLTQyLjEtOC40LTYzLjEtMTIuN2MtMjEuNjAxLTQuMy00My4yLTguNy02NC43LTEzYy0yMS40LTQuMy00Mi43LTguNjAxLTY0LjEwMS0xMi45Yy0yMC4zOTktNC4xLTQwLjgtOC4yLTYxLjE5OS0xMi4zDQoJCWMtMTguNy0zLjctMzcuMy03LjUtNTYtMTEuMmMtMTYuMi0zLjItMzIuNC02LjUtNDguNS05LjdjLTEyLjktMi42LTI1LjgtNS4xOTktMzguOC03LjhjLTguOS0xLjgtMTcuODAxLTMuNi0yNi43LTUuMzk5DQoJCWMtNC4xMDEtMC44MDEtOC4yLTEuNy0xMi4zLTIuNWMtMC4yLDAtMC40LTAuMTAxLTAuNjAxLTAuMTAxYzIuMiwxMC40LDEuMiwyMS41LTMuNiwzMS45Yy0xMC4xMDEsMjEuOC0zMy42MDEsMzMuMi01Ni4yLDI4LjgNCgkJYy02LjctMS4zLTE0LDEuMi0xNi45LDcuNGwtOSwxOS41Yy0yLjg5OSw2LjE5OSwwLDEzLjM5OSw1LjMwMSwxNy42OTljMSwwLjgwMSw3MjEuOCwzMzMuMTAxLDcyMi45OTksMzMzLjQNCgkJYzYuNywxLjMsMTQtMS4yLDE2LjktNy40bDktMTkuNWMyLjktNi4xOTksMC0xMy4zOTktNS4zLTE3LjY5OWMtMTgtMTQuMzAxLTI0LjYwMS0zOS42MDEtMTQuNS02MS40YzEwLjEtMjEuOCwzMy42LTMzLjIsNTYuMi0yOC44DQoJCWM2LjY5OSwxLjMsMTQtMS4yLDE2Ljg5OS03LjRsOS0xOS41YzIuOS02LjIsMC0xMy4zOTktNS4zLTE3LjdjLTE4LTE0LjMtMjQuNi0zOS42LTE0LjUtNjEuMzk5czMzLjYtMzMuMiw1Ni4yLTI4LjgNCgkJYzYuNywxLjMsMTQtMS4yLDE2LjktNy40bDktMTkuNWMyLjg5OS02LjIsMC0xMy40LTUuMzAxLTE3LjdjLTE4LTE0LjMtMjQuNi0zOS42LTE0LjUtNjEuNGMxMC4xMDEtMjEuOCwzMy42MDEtMzMuMTk5LDU2LjItMjguOA0KCQljNi43LDEuMywxNC0xLjIsMTYuOS03LjM5OWw5Ljg5OS0yMS42MDFjMi45LTYuMiwwLjItMTMuNS02LTE2LjM5OWwtMTA3LjY5OS00OS43TDg2OC41NjUsNDkyLjh6Ii8+DQoJPHBhdGggZD0iTTkuNjY1LDQ4NS45YzEuMiwwLjYsNzc5LjMsMTU2LjY5OSw3ODAuNiwxNTYuNjk5YzYuODAxLTAuMywxMy40LTQuNSwxNC43LTExLjFsNC4yLTIxYzEuMy02LjctMy4xLTEzLjEtOS4zLTE2DQoJCWMtMjAuOC05LjgtMzMuMTAxLTMyLjgtMjguNC01Ni40YzQuNy0yMy42LDI1LTQwLjEsNDgtNDEuMWM2LjgtMC4zLDEzLjQtNC41LDE0LjctMTEuMWwzLjEtMTUuNGwxLjEwMS01LjcNCgkJYzEuMy02LjctMy4xMDEtMTMuMS05LjMtMTZjLTIwLjgwMS05LjgtMzMuMTAxLTMyLjgtMjguNC01Ni4zOTljNC43LTIzLjYwMSwyNS00MC4xMDEsNDgtNDEuMTAxYzYuOC0wLjMsMTMuNC00LjUsMTQuNy0xMS4xDQoJCWw0LjItMjFjMS4zLTYuNy0zLjEwMS0xMy4xLTkuMzAxLTE2Yy0yMC44LTkuOC0zMy4xLTMyLjgtMjguMzk5LTU2LjRjNC43LTIzLjYsMjUtNDAuMSw0OC00MS4xYzYuOC0wLjMsMTMuMzk5LTQuNSwxNC43LTExLjENCgkJbDQuNjk5LTIzLjNjMS4zMDEtNi43LTMtMTMuMi05LjY5OS0xNC41YzAsMC03ODEuOS0xNTYuOC03ODIuNy0xNTYuOGMtNS44LDAtMTAuOSw0LjEtMTIuMSw5LjlsLTQuNywyMy4zDQoJCWMtMS4zLDYuNywzLjEsMTMuMSw5LjMsMTZjMjAuOCw5LjgsMzMuMSwzMi44LDI4LjQsNTYuNGMtNC43LDIzLjYtMjUsNDAuMS00OCw0MS4xYy02LjgwMSwwLjMtMTMuNCw0LjUtMTQuNywxMS4xbC00LjIsMjENCgkJYy0xLjMsNi43LDMuMSwxMy4xLDkuMywxNmMyMC44LDkuOCwzMy4xMDEsMzIuOCwyOC40LDU2LjRjLTQuNywyMy42LTI1LDQwLjEtNDgsNDEuMWMtNi44LDAuMy0xMy40LDQuNS0xNC43LDExLjFsLTQuMiwyMQ0KCQljLTEuMyw2LjcsMy4xMDEsMTMuMSw5LjMsMTZjMjAuODAxLDkuOCwzMy4xMDEsMzIuOCwyOC40LDU2LjRjLTQuNywyMy42MDEtMjUsNDAuMTAxLTQ4LDQxLjEwMWMtNi44LDAuMy0xMy40LDQuNS0xNC43LDExLjENCgkJbC00LjIsMjFDLTAuOTM1LDQ3Ni43LDMuNDY0LDQ4Myw5LjY2NSw0ODUuOXogTTY3Ni4xNjUsMjI5LjZjMi43LTEzLjUsMTUuOS0yMi4zLDI5LjQtMTkuNnMyMi4zLDE1LjksMTkuNiwyOS40bC0zMywxNjQuMg0KCQlsLTIwLjMsMTAxLjJjLTIuNCwxMS45LTEyLjgsMjAuMTAxLTI0LjUsMjAuMTAxYy0xLjYwMSwwLTMuMy0wLjItNC45LTAuNWMtMTMuNS0yLjctMjIuMy0xNS45LTE5LjYtMjkuNGwyMi43LTExMi45TDY3Ni4xNjUsMjI5LjYNCgkJeiBNMjI1LjM2NSwxMzkuMWMyLjctMTMuNSwxNS45LTIyLjMsMjkuNC0xOS42czIyLjMsMTUuOSwxOS42LDI5LjRsLTExLjQsNTYuN2wtMTIuODk5LDY0LjNsLTEwLjQsNTEuOGwtMTguNSw5Mi42DQoJCWMtMi4zOTksMTEuOS0xMi44LDIwLjEwMS0yNC41LDIwLjEwMWMtMS42LDAtMy4zLTAuMi00Ljg5OS0wLjVjLTAuNy0wLjEwMS0xLjQtMC4zMDEtMi0wLjVjLTEyLjQtMy42MDEtMjAuMTAxLTE2LjEwMS0xNy41LTI4LjkNCgkJbDMuNjk5LTE4LjdsOS43LTQ4LjRMMjI1LjM2NSwxMzkuMXoiLz4NCjwvZz4NCjxnPg0KPC9nPg0KPGc+DQo8L2c+DQo8Zz4NCjwvZz4NCjxnPg0KPC9nPg0KPGc+DQo8L2c+DQo8Zz4NCjwvZz4NCjxnPg0KPC9nPg0KPGc+DQo8L2c+DQo8Zz4NCjwvZz4NCjxnPg0KPC9nPg0KPGc+DQo8L2c+DQo8Zz4NCjwvZz4NCjxnPg0KPC9nPg0KPGc+DQo8L2c+DQo8Zz4NCjwvZz4NCjwvc3ZnPg0K',
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
                'taxonomies'            =>  array('event-categories','event-tags'),
                'supports'              =>  array('title', 'revisions', 'editor', 'thumbnail'),
            )
        );

        add_action('manage_posts_extra_tablenav', array($this, 'tablenavButtons'));

        $this->addTableColumn('cb', '<input type="checkbox">');
        $this->addTableColumn('title', __('Title'));
        $this->addTableColumn('location', __('Location'), true, function ($column, $postId) {
            $locationId = get_field('location', $postId);
            if (!isset($locationId[0])) {
                echo 'n/a';
                return;
            }
            echo '<a href="' . get_edit_post_link($locationId[0]) . '">' . get_the_title($locationId[0]) . '</a>';
        });
        $this->addTableColumn('contact', __('Contact'), true, function ($column, $postId) {
            $contactId = get_field('contacts', $postId);
            if (!isset($contactId[0])) {
                echo 'n/a';
                return;
            }

            echo '<a href="' . get_edit_post_link($contactId[0]) . '">' . get_the_title($contactId[0]) . '</a>';
        });
        $this->addTableColumn('import_client', __('Import client'), true, function ($column, $postId) {
            $contactId = get_post_meta($postId, 'import_client', true);
            if (!isset($contactId[0])) {
                return;
            }

            echo ucfirst(get_post_meta($postId, 'import_client', true));
        });
        $this->addTableColumn('acceptAndDeny', __('Public'), true, function ($column, $postId) {
            $metaAccepted = get_post_meta($postId, 'accepted');
            if (!isset($metaAccepted[0])) {
                add_post_meta($postId, 'accepted', 0);
                $metaAccepted[0] = 0;
            }
            $first = '';
            $second = '';
            if ($metaAccepted[0] == 1) {
                $first = 'hiddenElement';
            } elseif ($metaAccepted[0] == -1) {
                $second = 'hiddenElement';
            } elseif ($metaAccepted[0] == 0) {
                $first = 'hiddenElement';
                $second = 'hiddenElement';
                echo '<a href="'.get_edit_post_link( $postId ).'" title="This event needs to be edited before it can be published" class="button" postid="' . $postId . '">' . __('Edit draft') . '</a>';
            }
            echo '<a href="#" class="accept button-primary ' . $first . '" postid="' . $postId . '">' . __('Accept') . '</a>
            <a href="#" class="deny button-primary ' . $second . '" postid="' . $postId . '">' . __('Deny') . '</a>';
        });
        add_action('admin_head-post.php', array($this, 'hidePublishingActions'));
        add_action('publish_event', array($this, 'setAcceptedOnPublish'), 10, 2);
        add_filter('post_class', array($this, 'changeAcceptanceColor'));
        add_action('save_post', array($this, 'saveEventOccasions'), 10, 3);
        add_action('save_post', array($this, 'saveRecurringEvents'), 10, 3);
        add_action('delete_post', array($this, 'deleteEventOccasions'), 10);
        add_filter('acf/validate_value/name=end_date', array($this, 'validateEndDate'), 10, 4);
        add_filter('acf/validate_value/name=door_time', array($this, 'validateDoorTime'), 10, 4);
        add_filter('acf/validate_value/name=occasions', array($this, 'validateOccasion'), 10, 4);
        add_filter('acf/validate_value/name=rcr_rules', array($this, 'validateOccasion'), 10, 4);
        add_filter('acf/validate_value/name=rcr_end_time', array($this, 'validateRcrEndTime'), 10, 4);
        add_filter('acf/validate_value/name=rcr_door_time', array($this, 'validateRcrDoorTime'), 10, 4);
        add_filter('acf/validate_value/name=rcr_end_date', array($this, 'validateRcrEndDate'), 10, 4);
    }

    /**
     * Save event_occasions table when an event is published or updated.
     * @param  int  $post_id event post id
     * @param  post $post The post object.
     * @param  bool $update Whether this is an existing post being updated or not.
     */
    public function saveEventOccasions($post_id, $post, $update)
    {
        $slug = 'event';
        if ($slug != $post->post_type) {
            return;
        }
        if ($update) {
            global $wpdb;
            $db_occasions = $wpdb->prefix . "occasions";
            $wpdb->delete($db_occasions, array( 'event' => $post_id ), array( '%d' ));
            $repeater  = 'occasions';
            $count = intval(get_post_meta($post_id, $repeater, true));
            for ($i=0; $i<$count; $i++) {
                $getField   = $repeater.'_'.$i.'_'.'start_date';
                $value1     = get_post_meta($post_id, $getField, true);
                $timestamp  = strtotime($value1);
                $getField2  = $repeater.'_'.$i.'_'.'end_date';
                $value2     = get_post_meta($post_id, $getField2, true);
                $timestamp2 = strtotime($value2);
                $getField3  = $repeater.'_'.$i.'_'.'door_time';
                $value3     = get_post_meta($post_id, $getField3, true);
                if (empty($value3)) {
                    $timestamp3 = null;
                } else {
                    $timestamp3 = strtotime($value3);
                }

                $wpdb->insert($db_occasions, array('event' => $post_id, 'timestamp_start' => $timestamp, 'timestamp_end' => $timestamp2, 'timestamp_door' => $timestamp3));
            }
        } else {
            return;
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
        $slug = 'event';
        if ($slug != $post->post_type) {
            return;
        }
        if ($update) {
            $repeater  = 'rcr_rules';
            $rcr_count = intval(get_post_meta($post_id, $repeater, true));
            if ($rcr_count > 0) {
                global $wpdb;
                $db_occasions = $wpdb->prefix . "occasions";
                for ($i=0; $i < $rcr_count; $i++) {
                    $startTime = $repeater.'_'.$i.'_'.'rcr_start_time';
                    $startTimeValue = get_post_meta($post_id, $startTime, true);
                    $endTime  = $repeater.'_'.$i.'_'.'rcr_end_time';
                    $endTimeValue = get_post_meta($post_id, $endTime, true);
                    $doorTime  = $repeater.'_'.$i.'_'.'rcr_door_time';
                    $doorTimeValue = get_post_meta($post_id, $doorTime, true);
                    $weekDay  = $repeater.'_'.$i.'_'.'rcr_week_day';
                    $weekDayValue = get_post_meta($post_id, $weekDay, true);
                    $startDate  = $repeater.'_'.$i.'_'.'rcr_start_date';
                    $startDateValue = get_post_meta($post_id, $startDate, true);
                    $endDate  = $repeater.'_'.$i.'_'.'rcr_end_date';
                    $endDateValue = get_post_meta($post_id, $endDate, true);
                    // Save recurring dates to array
                    $recurringDates = array();
                    for ($j = strtotime($weekDayValue, strtotime($startDateValue)); $j <= strtotime($endDateValue); $j = strtotime('+1 week', $j)) {
                        $recurringDates[] = $j;
                    }
                    // Save exceptions to array
                    $exceptionDates = array();
                    $exc_count = intval(get_post_meta($post_id, $repeater.'_'.$i.'_'.'rcr_exceptions', true));
                    if ($exc_count > 0) {
                        for ($k=0; $k < $exc_count; $k++) {
                            $exceptionDates[] = strtotime(get_post_meta($post_id, $repeater.'_'.$i.'_'.'rcr_exceptions'.'_'.$k.'_'.'rcr_exc_date', true));
                        }
                    }
                    // Remove all exception dates from array
                    $filteredDates = array_diff($recurringDates, $exceptionDates);

                    // Save to event_occasions
                    foreach ($filteredDates as $key => $val) {
                        $timestampStart = strtotime(date('Y:m:d', $val).' '.$startTimeValue);
                        $timestampEnd = strtotime(date('Y:m:d', $val).' '.$endTimeValue);
                        if (empty($doorTimeValue)) {
                            $timestampDoor = null;
                        } else {
                            $timestampDoor = strtotime(date('Y:m:d', $val).' '.$doorTimeValue);
                        }

                        $wpdb->insert($db_occasions, array('event' => $post_id, 'timestamp_start' => $timestampStart, 'timestamp_end' => $timestampEnd, 'timestamp_door' => $timestampDoor));
                    }
                }
            }
        } else {
            return;
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
        if ($end_value <= $start_value) {
            $valid = 'End date must be after start date';
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
        if ($door_value > $start_value) {
            $valid = 'Door time cannot be after start date';
        }
        return $valid;
    }

    /**
     * Check if occasion och recurrence rules are set.
     */
    public function validateOccasion($valid, $value, $field, $input)
    {
        if (!$valid) {
            return $valid;
        }
        $occasions = $_POST['acf']['field_5761106783967'];
        $rcr_rules = $_POST['acf']['field_57d2749e3bf4d'];
        if (empty($occasions) && empty($rcr_rules)) {
            $valid = 'Please add occasion or recurrence rule';
        }
        return $valid;
    }

    /**
     * Validate recurring rules "end time" to be 'greater' than start time.
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
            $valid = 'End time must be after start time';
        }
        return $valid;
    }

    /**
     * Validate recurring rules "door time" to be 'greater' than start time.
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
            $valid = 'Door time cannot be after start time';
        }
        return $valid;
    }

    /**
     * Validate recurring rules interval "end date" to be 'greater' than start date.
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
        if ($end_value <= $start_value) {
            $valid = 'End date must be after start time';
        }
        return $valid;
    }


    /**
     * Change background color of event in list depending of it's meta_value 'accepted'
     * @param  array $classes
     * @return array $classes
     */
    public function changeAcceptanceColor($classes)
    {
        $postAndId = explode('-', $classes[3]);
        if ($postAndId[0] == 'post') {
            $metaAccepted = get_post_meta($postAndId[1], 'accepted');
            if ($metaAccepted[0] == -1) {
                $classes[] = "red";
            } elseif ($metaAccepted[0] == 1) {
                $classes[] = "green";
            }
        }
        return $classes;
    }

    /**
     * When publish are clicked we are either creating the meta 'accepted' with value 1 or update it
     * @param int $ID event post id
     * @param $post wordpress post object
     */
    public function setAcceptedOnPublish($ID, $post)
    {
        $metaAccepted = get_post_meta($ID, 'accepted');
        if (!isset($metaAccepted[0])) {
            add_post_meta($ID, 'accepted', 1);
        } else {
            update_post_meta($ID, 'accepted', 1);
        }
    }

    /**
     * Hiding the option to change post_status on when in a event, instead use the buttons in event list
     * @return void
     */
    public function hidePublishingActions()
    {
        $my_post_type = 'event';
        global $post;
        if ($post->post_type == $my_post_type) {
            echo '<style type="text/css">
                #misc-publishing-actions .misc-pub-section.misc-pub-post-status,#minor-publishing-actions
                {
                    display:none;
                }
            </style>';
        }
    }

    /**
     * Add buttons to start parsing xcap and Cbis
     * @return void
     */
    public function tablenavButtons($which)
    {
        global $current_screen;

        if ($current_screen->id != 'edit-event' || $which != 'top') {
            return;
        }

        if (current_user_can('manage_options')) {
            echo '<div class="alignleft actions" style="position: relative;">';
                //echo '<a href="' . admin_url('options.php?page=import-events') . '" class="button-primary" id="post-query-submit">Import XCAP</a>';
                //echo '<a href="' . admin_url('options.php?page=import-cbis-events') . '" class="button-primary" id="post-query-submit">Import CBIS</a>';
                echo '<div class="button-primary extraspace" id="xcap">' . __('Import XCAP') . '</div>';
            echo '<div class="button-primary extraspace" id="cbis">' . __('Import CBIS') . '</div>';
            echo '<div class="button-primary extraspace" id="occasions">Collect event timestamps</div>';
                //echo '<div id="importResponse"></div>';
            echo '</div>';
        }
    }
}
