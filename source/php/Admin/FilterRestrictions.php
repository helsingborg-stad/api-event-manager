<?php

namespace HbgEventImporter\Admin;

/**
 * Filter restrictions for events on edit.php
 */
class FilterRestrictions
{
    public function __construct()
    {
        add_filter('months_dropdown_results', '__return_empty_array');
        add_action('pre_get_posts', array($this, 'filterEventsByGroups'), 100);
        add_action('restrict_manage_posts', array($this, 'restrictEventsByCategory'), 100);
        add_action('restrict_manage_posts', array($this, 'restrictEventsByGroups'), 100);
        add_filter('parse_query', array($this, 'applyFilterRestrictions'), 100);
        add_action('pre_get_posts', array($this, 'applyIntervalRestriction'), 100);
        add_action('restrict_manage_posts', array($this, 'restrictEventsByDates'));
    }

    /**
     * Filter post types between selected dates
     * @param $postType
     */
    public function restrictEventsByDates($postType)
    {
        if ($postType !== 'event') {
            return;
        }

        $from = (isset($_GET['restrictDateFrom']) && $_GET['restrictDateFrom']) ? $_GET['restrictDateFrom'] : '';
        $to = (isset($_GET['restrictDateTo']) && $_GET['restrictDateTo']) ? $_GET['restrictDateTo'] : '';

        echo '<input type="text" name="restrictDateFrom" autocomplete="off" placeholder="' . __('Date from', 'event-manager') . '" value="' . $from . '" />
        <input type="text" name="restrictDateTo" autocomplete="off" placeholder="' . __('Date to', 'event-manager') . '" value="' . $to . '" />';
    }

    /**
     * Return all term children
     * @param  int $id term id
     * @return array
     */
    public static function getTermChildren($id)
    {
        $groups = get_field('event_user_groups', 'user_' . $id);
        if (is_array($groups) && !empty($groups)) {
            foreach ($groups as $group) {
                if (!empty(get_term_children($group, 'user_groups'))) {
                    $groups = array_merge($groups, get_term_children($group, 'user_groups'));
                }
            }

            return array_unique($groups);
        }
    }

    /**
     * Filter post types by users publishing groups
     * @param object $query WP Query
     * @return object
     */
    public function filterEventsByGroups($query)
    {
        global $pagenow, $wp_post_types, $post_type;

        if (current_user_can('administrator') || current_user_can('editor') || current_user_can('guide_administrator')) {
            return $query;
        }

        $postTypes = get_option('options_event_group_select');

        if (is_array($postTypes) && !empty($postTypes)) {
            foreach ($postTypes as $p) {
                if (isset($wp_post_types[$p]) && is_object($wp_post_types[$p]) && $pagenow == 'edit.php' && $post_type == $p) {
                    add_filter('posts_join', array($this, 'groupFilterJoin'));
                    add_filter('posts_where', array($this, 'groupFilterWhere'), 10, 2);
                    add_filter('posts_groupby', array($this, 'groupFilterGroupBy'));
                    add_filter('views_edit-' . $p, array($this, 'updateEventCounters'));
                }
            }
        }

        return $query;
    }

    /**
     * Join taxonomies and postmeta to sql statement
     * @param  string $join current join sql statement
     * @return string       updated statement
     */
    public function groupFilterJoin($join)
    {
        global $wpdb;

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
        $groups = $this->getTermChildren($id);
        $group_string = ($groups) ? implode(',', $groups) : false;

        $where .= " AND ($wpdb->posts.post_author = $id ";
        $where .= ($group_string) ? " OR ($wpdb->term_taxonomy.taxonomy = 'user_groups' AND $wpdb->term_taxonomy.term_id IN($group_string))" : '';
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
        return $wpdb->posts . '.ID';
    }

    /**
     * Update event counters when filtering by publishing groups
     * @param  array $views array with links markup
     * @return array
     */
    public function updateEventCounters($views)
    {
        $postType = get_query_var('post_type');

        $newViews = array(
            'all' => __('All', 'event-manager'),
            'publish' => __('Published', 'event-manager'),
            'private' => __('Private', 'event-manager'),
            'pending' => __('Pending Review', 'event-manager'),
            'future' => __('Scheduled', 'event-manager'),
            'draft' => __('Draft', 'event-manager'),
            'trash' => __('Trash', 'event-manager')
        );

        foreach ($newViews as $view => $name) {
            $query = array(
                'post_type' => $postType
            );

            if ($view == 'all') {
                $query['all_posts'] = 1;
                $class = (get_query_var('all_posts') == 1 || get_query_var('post_status') == '') ? ' class="current"' : '';
                $urlQueryVar = 'all_posts=1';
            } else {
                $query['post_status'] = $view;
                $class = (get_query_var('post_status') == $view) ? ' class="current"' : '';
                $urlQueryVar = 'post_status=' . $view;
            }

            $result = new \WP_Query($query);

            if ($result->found_posts > 0) {
                $views[$view] = sprintf(
                    '<a href="%s"' . $class . '>' . __($name) . ' <span class="count">(%d)</span></a>',
                    admin_url('edit.php?' . $urlQueryVar . '&post_type=' . $postType),
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
        global $post_type, $wp_query;

        if ($post_type !== 'event') {
            return;
        }

        $count = (current_user_can('editor') || current_user_can('administrator')) ? true : false;

        $taxonomy = 'event_categories';
        $term = isset($wp_query->query['event_categories']) ? $wp_query->query['event_categories'] : '';

        wp_dropdown_categories(array(
            'show_option_all' => __('All categories', 'event-manager'),
            'taxonomy' => $taxonomy,
            'name' => 'event_categories',
            'orderby' => 'name',
            'selected' => $term,
            'hierarchical' => true,
            'depth' => 3,
            'show_count' => $count,
            'hide_empty' => true,
            'hide_if_empty' => true,
        ));
    }

    /**
     * Add dropdown list with users publishing groups to use as filter
     * @return void
     */
    public function restrictEventsByGroups()
    {
        global $post_type, $wp_query;

        $post_types = get_field('event_group_select', 'option');
        if (!in_array($post_type, (array)$post_types)) {
            return;
        }

        if (current_user_can('editor') || current_user_can('administrator')) {
            $groups = array();
        } else {
            $id = get_current_user_id();
            $groups = $this->getTermChildren($id);

            if (empty($groups)) {
                return;
            }
        }

        $taxonomy = 'user_groups';
        $term = isset($wp_query->query['user_groups']) ? $wp_query->query['user_groups'] : '';

        wp_dropdown_categories(array(
            'show_option_all' => __('All groups', 'event-manager'),
            'taxonomy' => $taxonomy,
            'name' => 'user_groups',
            'orderby' => 'name',
            'selected' => $term,
            'hierarchical' => true,
            'show_count' => false,
            'hide_empty' => true,
            'hide_if_empty' => true,
            'include' => $groups,
        ));
    }

    /**
     * Apply taxonomy search filter
     * @param object $query WP query
     * @return void
     */
    public function applyFilterRestrictions($query)
    {
        global $pagenow;
        $qv =& $query->query_vars;

        if ($pagenow == 'edit.php' && isset($qv['event_categories']) && is_numeric($qv['event_categories'])) {
            $term = get_term_by('id', $qv['event_categories'], 'event_categories');
            $qv['event_categories'] = ($term ? $term->slug : '');
        }

        if ($pagenow == 'edit.php' && isset($qv['user_groups']) && is_numeric($qv['user_groups'])) {
            $term = get_term_by('id', $qv['user_groups'], 'user_groups');
            $qv['user_groups'] = ($term ? $term->slug : '');
        }
    }

    /**
     * Apply time interval and date filter
     * @param  object $query WP_Query object
     */
    public function applyIntervalRestriction($query)
    {
        global $pagenow;
        global $wpdb;

        if (is_admin()
            && $query->is_main_query()
            && $pagenow === 'edit.php'
            && $_GET['post_type'] === 'event'
            && ((!empty($_GET['restrictDateFrom']) || !empty($_GET['restrictDateTo'])))) {

            $date_begin = (!empty($_GET['restrictDateFrom'])) ? strtotime($_GET['restrictDateFrom']) : strtotime('- 3 years', strtotime($_GET['restrictDateTo']));
            $date_end = (!empty($_GET['restrictDateTo'])) ? strtotime('tomorrow', strtotime($_GET['restrictDateTo'])) - 1 : strtotime('+ 3 years', strtotime($_GET['restrictDateFrom']));

            $db_occasions = $wpdb->prefix . "occasions";
            // Query to get events occurring between certain dates
            $new_query = "
                    SELECT      $db_occasions.event as id
                    FROM        $db_occasions
                    WHERE       ($db_occasions.timestamp_start
                                BETWEEN {$date_begin} AND {$date_end}
                                OR $db_occasions.timestamp_end
                                BETWEEN {$date_begin} AND {$date_end})
                    GROUP BY $db_occasions.event";

            $results = $wpdb->get_results($new_query, ARRAY_A);
            foreach ($results as &$result) {
                $result = $result['id'];
            }

            if (!empty($results)) {
                $query->set('post__in', $results);
            } else {
                $query->set('post__in', array(0));
            }
        }

    }
}
