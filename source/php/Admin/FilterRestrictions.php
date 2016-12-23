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

        add_action('pre_get_posts', array($this, 'filterEventsByGroups'), 100);
    }



    /**
     * Filter event list by users publishing groups
     * @param  object $query object WP Query
     */
    public function filterEventsByGroups($query) {

        if (is_admin() && ! current_user_can('editor') && ! current_user_can('administrator')) {

            add_filter('posts_join', array($this, 'groupFilterJoin'));
            add_filter('posts_where', array($this, 'groupFilterWhere'), 10, 2);
            add_filter('posts_groupby', array($this, 'groupFilterGroupBy'));

            // remove_filter('posts_join', array($this, 'groupFilterJoin'));
            // remove_filter('posts_where', array($this, 'groupFilterWhere'), 10, 2);
            // remove_filter('posts_groupby', array($this, 'groupFilterGroupBy'));
            return $query;

        //add_filter('views_edit-event', array($this, 'updateEventCounters'));

        }
    }

    // Join for searching metadata
    function groupFilterJoin($join)
    {
        global $wp_query, $wpdb;
        $join .= "LEFT JOIN $wpdb->postmeta ON $wpdb->posts.ID = $wpdb->postmeta.post_id ";
        $join .= " LEFT JOIN $wpdb->term_relationships ON ($wpdb->posts.ID = $wpdb->term_relationships.object_id) ";
        $join .= " LEFT JOIN $wpdb->term_taxonomy ON ($wpdb->term_relationships.term_taxonomy_id = $wpdb->term_taxonomy.term_taxonomy_id) ";

        return $join;
    }

    function groupFilterWhere($where, $wp_query)
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

    function groupFilterGroupBy($groupby) {
        global $wpdb;
        $groupby = "{$wpdb->posts}.ID";
        return $groupby;
    }

    /**
     * FIXA
     * Update event counters when filtering by publishing groups
     * @param  [type] $views [description]
     * @return [type]        [description]
     */
    public function updateEventCounters( $views ) {
        $post_type = get_query_var('post_type');
        $author = get_current_user_id();

        // unset($views['mine']);

        $new_views = array(
                'all'       => __('All', 'event-manager'),
                'publish'   => __('Published', 'event-manager'),
                'private'   => __('Private', 'event-manager'),
                'pending'   => __('Pending Review', 'event-manager'),
                'future'    => __('Scheduled', 'event-manager'),
                'draft'     => __('Draft', 'event-manager'),
                'trash'     => __('Trash', 'event-manager')
                );

        foreach ($new_views as $view => $name ) {
            $query = array(
                'author'      => $author,
                'post_type'   => $post_type
            );

            if($view == 'all') {
                $query['all_posts'] = 1;
                $class = ( get_query_var('all_posts') == 1 || get_query_var('post_status') == '' ) ? ' class="current"' : '';
                $url_query_var = 'all_posts=1';

            } else {
                $query['post_status'] = $view;
                $class = ( get_query_var('post_status') == $view ) ? ' class="current"' : '';
                $url_query_var = 'post_status='.$view;
            }

            $result = new \WP_Query($query);
            if($result->found_posts > 0) {

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











    function filter_posts_list($query)
    {
        //$pagenow holds the name of the current page being viewed
         global $pagenow, $typenow;

        //$current_user uses the get_currentuserinfo() method to get the currently logged in user's data
         global $current_user;

            //Shouldn't happen for the admin, but for any role with the edit_posts capability and only on the posts list page, that is edit.php
            if(! current_user_can('administrator') && current_user_can('edit_posts') && ('edit.php' == $pagenow) && $typenow == 'event') {
                echo "string";
                //global $query's set() method for setting the author as the current user's id
                $query->set('author', $current_user->ID);
            }
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
