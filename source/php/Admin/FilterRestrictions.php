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
            $taxonomy = 'event-categories';
            $term = isset($wp_query->query['event-categories']) ? $wp_query->query['event-categories'] :'';
            wp_dropdown_categories(array(
                'show_option_all' =>  __("All categories"),
                'taxonomy'        =>  $taxonomy,
                'name'            =>  'event-categories',
                'orderby'         =>  'name',
                'selected'        =>  $term,
                'hierarchical'    =>  true,
                'depth'           =>  3,
                'show_count'      =>  true,
                'hide_empty'      =>  true,
            ));
        }
    }

    public function applyCategoryRestriction($query)
    {
        global $pagenow;
        $qv =& $query->query_vars;
        if ($pagenow=='edit.php' && isset($qv['event-categories']) && is_numeric($qv['event-categories'])) {
            $term = get_term_by('id', $qv['event-categories'], 'event-categories');
            $qv['event-categories'] = ($term ? $term->slug : '');
        }
    }

    public function restrictEventsByInterval($post_type)
    {
        if (isset($_GET['time_interval'])) {
            switch ($_GET['time_interval']) {
            case '1':
                $value1 = ' selected';
            break;
            case '2':
                $value2 = ' selected';
            break;
            case '3':
                $value3 = ' selected';
            break;
            case '4':
                $value4 = ' selected';
            break;
            case '3':
                $value5 = ' selected';
            break;
          }
        }

        if ($post_type == 'event') {
            echo '<select name="time_interval">';
            echo '<option value>Time interval</option>';
            echo '<option value="1"' . $value1 . '>Today</option>';
            echo '<option value="2"' . $value2 . '>Tomorrow</option>';
            echo '<option value="3"' . $value3 . '>This week</option>';
            echo '<option value="4"' . $value4 . '>This month</option>';
            echo '<option value="5"' . $value5 . '>Passed events</option>';
            echo '</select>';
        }
    }

    public function applyIntervalRestriction($query)
    {
        global $pagenow;

        if (isset($_GET['time_interval'])) {
            $time_now = strtotime("midnight now");
            $date_begin = date('Y-m-d H:i:s', $time_now);
            switch (esc_attr($_GET['time_interval'])) {
            case '1':
                $tomorrow = strtotime("tomorrow", $time_now) - 1;
                $date_end = date('Y-m-d H:i:s', $tomorrow);
            break;
            case '2';
                $tomorrow = strtotime('tomorrow', $time_now);
                $date_begin = date('Y-m-d H:i:s', $tomorrow);
                $tomorrow_end = strtotime("midnight tomorrow", $tomorrow) - 1;
                $date_end = date('Y-m-d H:i:s', $tomorrow_end);
            break;
            case '3':
                $monday = strtotime('midnight monday this week', $time_now);
                $date_begin = date('Y-m-d H:i:s', $monday);
                $end_of_week = strtotime("+ 1 week", $monday) - 1;
                $date_end = date('Y-m-d H:i:s', $end_of_week);
            break;
            case '4':
                $month_start = strtotime('midnight first day of this month', $time_now);
                $date_begin = date('Y-m-d H:i:s', $month_start);
                $month_end = strtotime('midnight first day of next month', $time_now) - 1;
                $date_end = date('Y-m-d H:i:s', $month_end);
            break;
            case '5':
                $date_begin = 0;
                $yesterday = strtotime("today", $time_now) - 1;
                $date_end = date('Y-m-d H:i:s', $yesterday);
            break;
            }
        }

        if ($query->is_admin && $pagenow == 'edit.php' && isset($_GET['time_interval']) && $_GET['time_interval'] != '' && $_GET['post_type'] == 'event') {
            $meta_key_query = array(
                'meta_query'    => array(
                    'name'      => 'start_date',
                    'compare'   => 'BETWEEN',
                    'value'     => array($date_begin, $date_end),
                    'type'      => 'DATETIME'
                )
              );
            $query->set('meta_query', $meta_key_query);
        }
    }
}
