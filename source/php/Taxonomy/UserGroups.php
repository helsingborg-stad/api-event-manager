<?php

namespace HbgEventImporter\Taxonomy;

class UserGroups
{
    public function __construct()
    {
        add_action('init', array($this, 'registerTaxonomy'), 9);
        add_action('admin_menu', array($this, 'manageAdminMenu'), 999);
        add_action('show_user_profile', array($this, 'displayUserGroups'));
        add_action('edit_user_profile', array($this, 'displayUserGroups'));
        add_filter('parent_file', array($this, 'highlightAdminMenu'));
        add_filter('taxonomy_parent_dropdown_args', array($this, 'limitDropdownDepth'), 10, 2);
        add_filter('acf/fields/taxonomy/wp_list_categories/name=user_groups', array($this, 'filterGroupTaxonomy'), 10, 3);
        add_filter('acf/fields/taxonomy/wp_list_categories/name=event_user_groups', array($this, 'filterGroupTaxonomy'), 10, 3);
        add_filter('acf/fields/taxonomy/query/name=guide_taxonomy_user_group', array($this, 'filterGroupTaxonomy'), 10, 3);
        add_filter('list_terms_exclusions', array($this, 'excludeEventGroups'), 10, 2);
    }

    /**
     * Only display assigned user groups for event admins
     * @param  string $exclusions Clauses of the terms query
     * @return string             Altered exclusion string
     */
    public function excludeEventGroups($exclusions)
    {
        if (current_user_can('administrator') || current_user_can('editor')) {
            return $exclusions;
        }

        require_once(ABSPATH . 'wp-admin/includes/screen.php');
        $current_screen = get_current_screen();
        remove_filter( 'list_terms_exclusions', array($this, 'excludeEventGroups'), 10, 2 );

        if (is_object($current_screen) && $current_screen->id == 'edit-user_groups' && $current_screen->taxonomy == 'user_groups' ) {
            $user = wp_get_current_user();
            $user_groups = \HbgEventImporter\Admin\FilterRestrictions::getTermChildren($user->ID);
            $groups = ($user_groups) ? implode(',', $user_groups) : '0';
            $exclusions = ' AND' . ' t.term_id IN (' . $groups . ')';
        }

        add_filter('list_terms_exclusions', array($this, 'excludeEventGroups'), 10, 2);

        return $exclusions;
    }

    /**
     * Filter to display users group taxonomies
     * @param  array  $args   An array of arguments passed to the wp_list_categories function
     * @param  array  $field  An array containing all the field settings
     * @return array  $args
     */
    public function filterGroupTaxonomy($args, $field)
    {
        $current_user = wp_get_current_user();

        // Return if admin or editor
        if (current_user_can('administrator') || current_user_can('editor')) {
            return $args;
        }

        $id = $current_user->ID;
        $groups = \HbgEventImporter\Admin\FilterRestrictions::getTermChildren($id);

        // Return the assigned groups for the user
        if (! empty($groups) && is_array($groups)) {
            $args['include'] = $groups;
        } else {
            return false;
        }

        return $args;
    }

    public function registerTaxonomy()
    {
        $labels = array(
            'name'                  => _x('Groups', 'Taxonomy plural name', 'event-manager'),
            'singular_name'         => _x('Group', 'Taxonomy singular name', 'event-manager'),
            'search_items'          => __('Search groups', 'event-manager'),
            'popular_items'         => __('Popular groups', 'event-manager'),
            'all_items'             => __('All groups', 'event-manager'),
            'parent_item'           => __('Parent group', 'event-manager'),
            'parent_item_colon'     => __('Parent group', 'event-manager'),
            'edit_item'             => __('Edit group', 'event-manager'),
            'update_item'           => __('Update group', 'event-manager'),
            'add_new_item'          => __('Add new group', 'event-manager'),
            'new_item_name'         => __('New group', 'event-manager'),
            'add_or_remove_items'   => __('Add or remove groups', 'event-manager'),
            'choose_from_most_used' => __('Choose from most used groups', 'event-manager'),
            'menu_name'             => __('Groups', 'event-manager'),
        );

        $capabilities = array(
            'manage_terms' => 'edit_users',
            'edit_terms'   => 'edit_users',
            'delete_terms' => 'edit_users',
            'assign_terms' => 'read',
        );

        $args = array(
            'capabilities'          => $capabilities,
            'labels'                => $labels,
            'public'                => true,
            'show_in_nav_menus'     => false,
            'show_admin_column'     => true,
            'hierarchical'          => true,
            'show_tagcloud'         => false,
            'show_ui'               => true,
            'query_var'             => true,
            'rewrite'               => true,
            'meta_box_cb'           => false,
        );

        $user_groups = get_field('event_group_select', 'option');
        register_taxonomy('user_groups', $user_groups, $args);
    }

    /**
     * Update taxonomy dropdown args
     * @param  array  $args     args
     * @param  string $taxonomy taxonomy
     * @return array
     */
    public function limitDropdownDepth($args, $taxonomy) {
        if ($taxonomy != 'user_groups') return $args;
        if (!current_user_can('administrator')) {
             $args['show_option_none'] = '';
        }
        $args['depth'] = '2';
        return $args;
    }

    /**
     * Hide groups from post type menus. Add user group to Users menu.
     * @return void
     */
    public function manageAdminMenu()
    {
        $post_types = get_post_types(array('public' => true), 'names');
        if (is_array($post_types) && ! empty($post_types)) {
            foreach ($post_types as $val) {
                remove_submenu_page('edit.php?post_type=' . $val, 'edit-tags.php?taxonomy=user_groups&amp;post_type=' . $val);
            }
        }

        add_submenu_page('users.php', __('User groups', 'event-manager'), __('User groups', 'event-manager'), 'add_users', 'edit-tags.php?taxonomy=user_groups');
    }

    /**
     * Highlighting the Users parent menu item
     * @param  string $parent parent string
     * @return string
     */
    public function highlightAdminMenu($parent = '')
    {
        global $pagenow;

        if (!empty($_GET['taxonomy']) && $pagenow == 'edit-tags.php' && $_GET['taxonomy'] == 'user_groups') {
            $parent = 'users.php';
        }

        return $parent;
    }

    /**
     * Adds a new section on user profile with the assigned groups.
     * @param object $user The user object currently being edited.
     */
    public function displayUserGroups($user)
    {
        // Return if admin or editor
        if (current_user_can('editor') || current_user_can('administrator') || current_user_can('guide_administrator') || current_user_can('event_administrator')) {
            return;
        }

        $id = 'user_' . $user->ID;
        $groups = get_field('event_user_groups', $id);

        ?>
            <h2><?php _e('Event publishing groups', 'event-manager') ?></h2>
            <table class="form-table">
                <tr>
                    <th><label for="groups"><?php _e('Assigned groups', 'event-manager'); ?></label></th>
                    <td>
                        <?php if (! empty($groups)) : ?>
                            <ul>
                                <?php foreach ($groups as $group) : ?>
                                    <li><?php echo get_term($group)->name; ?></li>
                                <?php endforeach; ?>
                        <?php else: ?>
                           <?php _e('There are no groups assigned to your account.', 'event-manager'); ?>
                        <?php endif; ?>
                    </td>
                </tr>
            </table>
        <?php
    }
}
