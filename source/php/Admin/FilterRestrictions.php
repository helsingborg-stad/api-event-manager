<?php

namespace HbgEventImporter\Admin;

/**
 * Filter restrictions for events on edit.php
 */

class FilterRestrictions
{
    public function __construct()
    {
        add_action('pre_get_posts', array($this, 'filterEventsByGroups'), 100);
        add_action('restrict_manage_posts', array($this, 'restrictEventsByCategory'), 100);
        add_action('restrict_manage_posts', array($this, 'restrictEventsByGroups'), 100);
        add_filter('parse_query', array($this, 'applyCategoryRestriction'), 100);
        add_action('restrict_manage_posts', array($this, 'restrictEventsByInterval'), 100);
        add_action('pre_get_posts', array($this, 'applyIntervalRestriction'), 100);
    }

    /**
     * Filter event list by users publishing groups
     * @param  object $query object WP Query
     */
    public function filterEventsByGroups($query)
    {
        global $pagenow, $post_type;

        if (is_admin() && $pagenow == 'edit.php' && $post_type == 'event' && ! current_user_can('editor') && ! current_user_can('administrator')) {
            add_filter('posts_join', array($this, 'groupFilterJoin'));
            add_filter('posts_where', array($this, 'groupFilterWhere'), 10, 2);
            add_filter('posts_groupby', array($this, 'groupFilterGroupBy'));
            add_filter('views_edit-event', array($this, 'updateEventCounters'));

            return $query;
        }
    }

    /**
     * Join taxonomies and postmeta to sql statement
     * @param  string $join current join sql statement
     * @return string       updated statement
     */
    public function groupFilterJoin($join)
    {
        global $wp_query, $wpdb;

        $join .= "LEFT JOIN $wpdb->postmeta ON $wpdb->posts.ID = $wpdb->postmeta.post_id ";
        $join .= " LEFT JOIN $wpdb->term_relationships AS term_rel ON ($wpdb->posts.ID = term_rel.object_id) ";
        $join .= " LEFT JOIN $wpdb->term_taxonomy ON (term_rel.term_taxonomy_id = $wpdb->term_taxonomy.term_taxonomy_id) ";

        return $join;
    }

    /**
     * Add where statements
     * @param  string $where current where statement
     * @return string        updated statement
     */
    public function groupFilterWhere($where)
    {
        global $wpdb;

        $id = get_current_user_id();
        $groups = get_field('event_user_groups', 'user_' . $id);
        $groups = (! empty($groups) && is_array($groups)) ? implode(', ', $groups) : false;

        $where .= " AND ($wpdb->posts.post_author = $id ";
        $where .= "OR ($wpdb->postmeta.meta_key = 'event_unbelonging_group' AND $wpdb->postmeta.meta_value = 1) ";
        $where .= ($groups) ? " OR ($wpdb->term_taxonomy.taxonomy = 'event_groups' AND $wpdb->term_taxonomy.term_id IN($groups))" : '';
        $where .= ") ";

        return $where;
    }

    /**
     * Add group by statement
     * @param  string $groupby current group by statement
     * @return string          updated statement
     */
    public function groupFilterGroupBy($groupby)
    {
        global $wpdb;
        $groupby = "{$wpdb->posts}.ID";
        return $groupby;
    }

    /**
     * Update event counters when filtering by publishing groups
     * @param  array $views array with links markup
     * @return array
     */
    public function updateEventCounters($views)
    {
        $post_type = get_query_var('post_type');

        $new_views = array(
                'all'       => __('All', 'event-manager'),
                'publish'   => __('Published', 'event-manager'),
                'private'   => __('Private', 'event-manager'),
                'pending'   => __('Pending Review', 'event-manager'),
                'future'    => __('Scheduled', 'event-manager'),
                'draft'     => __('Draft', 'event-manager'),
                'trash'     => __('Trash', 'event-manager')
                );

        foreach ($new_views as $view => $name) {
            $query = array(
                'post_type'   => $post_type
            );

            if ($view == 'all') {
                $query['all_posts'] = 1;
                $class = (get_query_var('all_posts') == 1 || get_query_var('post_status') == '') ? ' class="current"' : '';
                $url_query_var = 'all_posts=1';
            } else {
                $query['post_status'] = $view;
                $class = (get_query_var('post_status') == $view) ? ' class="current"' : '';
                $url_query_var = 'post_status='.$view;
            }

            $result = new \WP_Query($query);

            if ($result->found_posts > 0) {
                $views[$view] = sprintf(
                    '<a href="%s"'. $class .'>'.__($name).' <span class="count">(%d)</span></a>',
                    admin_url('edit.php?'.$url_query_var.'&post_type='.$post_type),
                    $result->found_posts
                );
            } else {
                unset($views[$view]);
            }
        }

        return $views;
    }

    /**
     * Add dropdown list with categories to use as filter
     * @return void
     */
    public function restrictEventsByCategory()
    {
        global $post_type;
        global $wp_query;

        $count = (current_user_can('editor') || current_user_can('administrator')) ? true : false;

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
                'show_count'      =>  $count,
                'hide_empty'      =>  true,
                'hide_if_empty'   =>  true,
            ));
        }
    }

    /**
     * Add dropdown list with users publishing groups to use as filter
     * @return void
     */
    public function restrictEventsByGroups()
    {
        global $post_type;
        global $wp_query;

        if (current_user_can('editor') || current_user_can('administrator')) {
            $groups = array();
        } else {
            $id = 'user_' . get_current_user_id();
            $groups = get_field('event_user_groups', $id);
            if (empty($groups)) {
                return;
            }
        }

        if ($post_type=='event') {
            $taxonomy = 'event_groups';
            $term = isset($wp_query->query['event_groups']) ? $wp_query->query['event_groups'] :'';
            wp_dropdown_categories(array(
                'show_option_all' =>  __('All groups', 'event-manager'),
                'taxonomy'        =>  $taxonomy,
                'name'            =>  'event_groups',
                'orderby'         =>  'name',
                'selected'        =>  $term,
                'hierarchical'    =>  false,
                'show_count'      =>  true,
                'hide_empty'      =>  true,
                'hide_if_empty'   =>  true,
                'include'         =>  $groups,
            ));
        }
    }

    /**
     * Apply taxonomy search filter
     * @return void
     */
    public function applyCategoryRestriction($query)
    {
        global $pagenow;
        $qv =& $query->query_vars;

        if ($pagenow=='edit.php' && isset($qv['event_categories']) && is_numeric($qv['event_categories'])) {
            $term = get_term_by('id', $qv['event_categories'], 'event_categories');
            $qv['event_categories'] = ($term ? $term->slug : '');
        }

        if ($pagenow=='edit.php' && isset($qv['event_groups']) && is_numeric($qv['event_groups'])) {
            $term = get_term_by('id', $qv['event_groups'], 'event_groups');
            $qv['event_groups'] = ($term ? $term->slug : '');
        }
    }

    /**
     * Add custom dropdown list with time interval filters
     * @param  string $post_type current post type
     */
    public function restrictEventsByInterval($post_type)
    {
        $timeInterval = (isset($_GET['time_interval'])) ? $_GET['time_interval'] : '';
        if ($post_type == 'event') {
            $str =  '<select name="time_interval">';
            $str .= '<option value>'.__('Time interval', 'event-manager').'</option>';
            $str .= '<option value="1"' . (($timeInterval == 1) ? ' selected' : '') . '>'.__('Today', 'event-manager').'</option>';
            $str .= '<option value="2"' . (($timeInterval == 2) ? ' selected' : '') . '>'.__('Tomorrow', 'event-manager').'</option>';
            $str .= '<option value="3"' . (($timeInterval == 3) ? ' selected' : '') . '>'.__('This week', 'event-manager').'</option>';
            $str .= '<option value="4"' . (($timeInterval == 4) ? ' selected' : '') . '>'.__('This month', 'event-manager').'</option>';
            $str .= '<option value="5"' . (($timeInterval == 5) ? ' selected' : '') . '>'.__('Passed events', 'event-manager').'</option>';
            $str .= '</select>';
            echo $str;
        }
    }

    /**
     * Apply time interval filter
     * @param  object $query WP_Query object
     */
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

            if (!empty($allEventIds)) {
                $query->set('post__in', $allEventIds);
            } else {
                $query->set('post__in',  array(0));
            }
        }
    }
}
