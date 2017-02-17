<?php

namespace HbgEventImporter\Taxonomy;

class UserGroups
{
    public function __construct()
    {
        add_action('init', array($this, 'registerTaxonomy'));
        add_action('admin_menu', array($this, 'manageAdminMenu'), 999);
        add_filter('parent_file', array($this, 'highlightAdminMenu'));
        add_action('show_user_profile', array($this, 'displayUserGroups'));
        add_action('edit_user_profile', array($this, 'displayUserGroups'));
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
            'manage_terms' => 'publish_pages',
            'edit_terms'   => 'publish_pages',
            'delete_terms' => 'publish_pages',
            'assign_terms' => 'read',
        );

        $args = array(
            'capabilities'          => $capabilities,
            'labels'                => $labels,
            'public'                => true,
            'show_in_nav_menus'     => false,
            'show_admin_column'     => true,
            'hierarchical'          => false,
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
        if (current_user_can('editor') || current_user_can('administrator')) {
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
