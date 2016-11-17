<?php

namespace HbgEventImporter\Admin;

/**
 * Filter restrictions for events on edit.php
 * Restrictions: category, time intervals
 */

class FilterRestrictions
{
    public function __construct()
    {
        add_action('restrict_manage_posts', array($this, 'restrictEventsByCategory'), 100);
        add_filter('parse_query', array($this, 'applyCategoryRestriction'), 100);
        add_action('restrict_manage_posts', array($this, 'restrictEventsByInterval'), 100);
        add_action('pre_get_posts', array($this, 'applyIntervalRestriction'), 100);
    }

    public function restrictEventsByCategory()
    {
        global $post_type;
        global $wp_query;
        if ($post_type=='event') {
            $taxonomy = 'event_categories';
            $term = isset($wp_query->query['event_categories']) ? $wp_query->query['event_categories'] :'';
            wp_dropdown_categories(array(
                'show_option_all' =>  __('All categories', 'event-manager'),
                'taxonomy'        =>  $taxonomy,
                'name'            =>  'event_categories',
                'orderby'         =>  'name',
                'selected'        =>  $term,
                'hierarchical'    =>  true,
                'depth'           =>  3,
                'show_count'      =>  true,
                'hide_empty'      =>  true,
                'hide_if_empty'   =>  true,
            ));
        }
    }

    public function applyCategoryRestriction($query)
    {
        global $pagenow;
        $qv =& $query->query_vars;
        if ($pagenow=='edit.php' && isset($qv['event_categories']) && is_numeric($qv['event_categories'])) {
            $term = get_term_by('id', $qv['event_categories'], 'event_categories');
            $qv['event_categories'] = ($term ? $term->slug : '');
        }
    }

    public function restrictEventsByInterval($post_type)
    {
        $timeInterval = (isset($_GET['time_interval'])) ? $_GET['time_interval'] : '';
        if ($post_type == 'event') {
            echo '<select name="time_interval">';
            echo '<option value>'.__('Time interval', 'event-manager').'</option>';
            echo '<option value="1"' . (($timeInterval == 1)?' selected':'') . '>'.__('Today', 'event-manager').'</option>';
            echo '<option value="2"' . (($timeInterval == 2)?' selected':'') . '>'.__('Tomorrow', 'event-manager').'</option>';
            echo '<option value="3"' . (($timeInterval == 3)?' selected':'') . '>'.__('This week', 'event-manager').'</option>';
            echo '<option value="4"' . (($timeInterval == 4)?' selected':'') . '>'.__('This month', 'event-manager').'</option>';
            echo '<option value="5"' . (($timeInterval == 5)?' selected':'') . '>'.__('Passed events', 'event-manager').'</option>';
            echo '</select>';
        }
    }

    public function applyIntervalRestriction($query)
    {
        global $pagenow;
        global $wpdb;

        if ($query->is_admin && $pagenow == 'edit.php' && isset($_GET['time_interval']) && $_GET['time_interval'] != '' && $_GET['post_type'] == 'event') {
            $time_now = strtotime("midnight now");
            switch (esc_attr($_GET['time_interval'])) {
        case '1':
            $date_begin = strtotime("midnight now");
            $date_end = strtotime("tomorrow", $time_now) - 1;
        break;
        case '2';
            $date_begin = strtotime('tomorrow', $time_now);
            $date_end = strtotime("midnight tomorrow", $date_begin) - 1;
        break;
        case '3':
            $date_begin = strtotime('midnight monday this week', $time_now);
            $date_end = strtotime("+ 1 week", $date_begin) - 1;
        break;
        case '4':
            $date_begin = strtotime('midnight first day of this month', $time_now);
            $date_end = strtotime('midnight first day of next month', $date_begin) - 1;
        break;
        case '5':
            $date_begin = 0;
            $date_end = strtotime("today", $time_now) - 1;
        break;
        }

            $db_occasions = $wpdb->prefix . "occasions";
            $joinquery =
        "
        SELECT      $wpdb->posts.ID
        FROM        $wpdb->posts
        LEFT JOIN   $db_occasions
                    ON $wpdb->posts.ID = $db_occasions.event
        WHERE       $wpdb->posts.post_type = %s
                    AND ($db_occasions.timestamp_start BETWEEN %d AND %d OR $db_occasions.timestamp_end BETWEEN %d AND %d)
                    ORDER BY $db_occasions.timestamp_start ASC
        "
        ;

            $completeQuery = $wpdb->prepare($joinquery, 'event', $date_begin, $date_end, $date_begin, $date_end);
            $results = $wpdb->get_results($completeQuery);

            $allEventIds = array();
            foreach ($results as $key => $value) {
                $allEventIds[] = $value->ID;
            }
        //$allEventIds = array_unique($allEventIds);

        if (!empty($allEventIds)) {
            $query->set('post__in', $allEventIds);
        } else {
            $query->set('post__in',  array(0));
        }
        }
    }
}
