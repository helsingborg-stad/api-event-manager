<?php

namespace HbgEventImporter\Admin;

/**
* Add user roles and capabilities
*/
class UserRoles
{
    public function __construct()
    {
        add_action('init', array($this, 'removeUserRoles'));
        add_action('admin_init', array($this, 'addCapabilities'));
        add_action('pre_get_users', array($this, 'filterUserList'));
        add_action('admin_menu', array($this, 'removeMenuItems'));
        add_action('current_screen', array($this, 'restrictUserPages'));
        add_action('delete_user', array($this, 'restrictDeleteUsers'));
        add_filter('editable_roles', array($this, 'filterEditableRoles'));
        add_filter('views_users', array($this, 'hideUserRoleQuicklinks'));
        add_filter('map_meta_cap', array($this, 'addAdminUserCap'), 10, 4);
        add_filter('enable_edit_any_user_configuration', '__return_true');
    }

    /**
     * Gives users with capability 'edit_users' to edit other users on multisite
     * @param array     $caps       Returns the user's actual capabilities
     * @param string    $cap        Capability name
     * @param int       $user_id    The user ID
     * @param array     $args       Adds the context to the cap. Typically the object ID
     */
    public function addAdminUserCap($caps, $cap, $user_id, $args)
    {
        foreach ($caps as $key => $capability) {
            if ($capability != 'do_not_allow') {
                continue;
            }

            switch ($cap) {
                case 'edit_user':
                case 'edit_users':
                    $caps[$key] = 'edit_users';
                    break;
                case 'delete_user':
                case 'delete_users':
                    $caps[$key] = 'delete_users';
                    break;
                case 'create_users':
                    $caps[$key] = $cap;
                    break;
            }
        }

        return $caps;
    }

    /**
     * Check if user has permissions before deletion
     * @param  int $user_id ID of the user to delete
     * @return void
     */
    public function restrictDeleteUsers($user_id)
    {
        if (current_user_can('administrator')) {
            return;
        }

        $this->checkPermission($user_id);
    }

    /**
     * Check if event admin have permission to edit user
     * @return void
     */
    public function restrictUserPages()
    {
        if (current_user_can('administrator')) {
            return;
        }

        $screen = get_current_screen();
        if ($screen->base == 'user-edit' && !empty($_GET['user_id'])) {
            $this->checkPermission($_GET['user_id']);
        }
    }

    public function checkPermission($profile_id)
    {
        global $current_user;

        $profile_user = get_userdata($profile_id);
        $profile_user_groups = \HbgEventImporter\Admin\FilterRestrictions::getTermChildren($profile_id);
        $current_user_groups = \HbgEventImporter\Admin\FilterRestrictions::getTermChildren($current_user->ID);

        if ((in_array('event_administrator', $profile_user->roles) || in_array('event_contributor', $profile_user->roles))
            && !is_super_admin($profile_id)
            && $profile_user_groups
            && $current_user_groups) {
            $matches = array_intersect($profile_user_groups, $current_user_groups);
            if ($matches) {
                return;
            }
        }

        wp_die(
            '<h1>' . __('Cheatin&#8217; uh?') . '</h1>' .
            '<p>' . __('Missing permissions') . '</p>',
            403
        );
    }

    /**
     * Hide menu items for non admins
     * @return void
     */
    public function removeMenuItems()
    {
        if (!current_user_can('administrator')) {
            remove_submenu_page('users.php', 'rest-oauth1-apps');
        }
    }

    /**
     * Remove certain editable user roles for non admins
     * @param  array $roles List of user roles
     * @return array        Altered list of roles
     */
    public function filterEditableRoles($roles)
    {
        if (!current_user_can('administrator')) {
            unset($roles['editor'], $roles['administrator'], $roles['guide_editor'], $roles['guide_administrator'], $roles['author']);
        }

        return $roles;
    }

    /**
     * Remove user role quicklinks for non admins
     * @param  array $views List of user role links
     * @return array
     */
    public function hideUserRoleQuicklinks($views)
    {
        if (!current_user_can('administrator')) {
            $views = array();
        }

        return $views;
    }

    /**
     * Filter user list for non admins
     * @param  obj $query User query
     * @return void
     */
    public function filterUserList($query)
    {
        if (!current_user_can('administrator')) {
            global $wpdb;

            $current_user = wp_get_current_user();
            $groups = \HbgEventImporter\Admin\FilterRestrictions::getTermChildren($current_user->ID);

            if ($groups) {
                $meta_query = array(
                    'relation' => 'OR'
                );

                foreach ($groups as $group) {
                    $meta_query[] = array(
                                        'key' => 'event_user_groups',
                                        'value' => '"' . $group . '"',
                                        'compare' => 'LIKE'
                                    );
                }

                $query->set('meta_key', 'event_user_groups');
                $query->set('meta_query', $meta_query);
                $query->set('role__in', array('event_administrator', 'event_contributor'));
            } else {
                $query->set('include', array(0));
            }
        }
    }

    /**
     * Create custom user roles
     * @return void
     */
    public static function createUserRoles()
    {
        add_role('event_administrator', __("Event administrator", 'event-manager'), array(
            'read' => true,
            'level_8' => true,
            'upload_files' => true
        ));
        add_role('guide_administrator', __("Guide administrator", 'event-manager'), array(
            'read' => true,
            'level_7' => true,
            'upload_files' => true
        ));
        add_role('event_contributor', __("Event contributor", 'event-manager'), array(
            'read' => true,
            'level_4' => true,
            'upload_files' => true
        ));
        add_role('guide_editor', __("Guide editor", 'event-manager'), array(
            'read' => true,
            'level_4' => true,
               'upload_files' => true
        ));
    }

    /**
     * Remove custom user roles
     * @return void
     */
    public static function removeCustomUserRoles()
    {
        $roles = array('guide_administrator', 'event_contributor', 'guide_editor', 'event_administrator');
        foreach ($roles as $role) {
            if (get_role($role)) {
                remove_role($role);
            }
        }
    }

    /**
     * Remove user roles
     * @return void
     */
    public function removeUserRoles()
    {
        if (get_role('author')) {
            remove_role('author');
        }
        if (get_role('subscriber')) {
            remove_role('subscriber');
        }
    }


    /**
     * Add user capabilities to custom post types
     */
    public function addCapabilities()
    {
        // Administrator
        $postTypes = array('event', 'location', 'sponsor', 'membership-card', 'guide', 'organizer', 'interactive_guide');
        $role = get_role('administrator');
        foreach ($postTypes as $key => $type) {
            $role->add_cap('edit_' . $type);
            $role->add_cap('read_' . $type);
            $role->add_cap('delete_' . $type);
            $role->add_cap('edit_' . $type . 's');
            $role->add_cap('edit_others_' . $type . 's');
            $role->add_cap('publish_' . $type . 's');
            $role->add_cap('read_private_' . $type . 's');
            $role->add_cap('delete_' . $type . 's');
            $role->add_cap('delete_private_' . $type . 's');
            $role->add_cap('delete_published_' . $type . 's');
            $role->add_cap('delete_others_' . $type . 's');
            $role->add_cap('edit_private_' . $type . 's');
            $role->add_cap('edit_published_' . $type . 's');
        }

        // Event administrator
        $postTypes = array('event', 'location', 'sponsor', 'membership-card', 'organizer', 'guide', 'interactive_guide');
        $role = get_role('event_administrator');
        if ($role) {
            foreach ($postTypes as $key => $type) {
                $role->add_cap('edit_' . $type);
                $role->add_cap('read_' . $type);
                $role->add_cap('delete_' . $type);
                $role->add_cap('edit_' . $type . 's');
                $role->add_cap('edit_others_' . $type . 's');
                $role->add_cap('publish_' . $type . 's');
                $role->add_cap('delete_' . $type . 's');
                $role->add_cap('delete_published_' . $type . 's');
                $role->add_cap('delete_others_' . $type . 's');
                $role->add_cap('edit_published_' . $type . 's');
            }
            $role->add_cap('edit_users');
            $role->add_cap('list_users');
            $role->add_cap('promote_users');
            $role->add_cap('create_users');
            $role->add_cap('add_users');
            $role->add_cap('delete_users');
        }

        // Editor
        $postTypes = array('event', 'location', 'sponsor', 'membership-card', 'guide', 'organizer', 'interactive_guide');
        $role = get_role('editor');
        foreach ($postTypes as $key => $type) {
            $role->add_cap('edit_' . $type);
            $role->add_cap('read_' . $type);
            $role->add_cap('delete_' . $type);
            $role->add_cap('edit_' . $type . 's');
            $role->add_cap('edit_others_' . $type . 's');
            $role->add_cap('publish_' . $type . 's');
            $role->add_cap('read_private_' . $type . 's');
            $role->add_cap('delete_' . $type . 's');
            $role->add_cap('delete_private_' . $type . 's');
            $role->add_cap('delete_published_' . $type . 's');
            $role->add_cap('delete_others_' . $type . 's');
            $role->add_cap('edit_private_' . $type . 's');
            $role->add_cap('edit_published_' . $type . 's');
        }

        // Event Contributor
        $postTypes = array('event', 'location', 'sponsor', 'membership-card', 'organizer');
        $role = get_role('event_contributor');
        if ($role) {
            foreach ($postTypes as $key => $type) {
                $role->add_cap('edit_' . $type);
                $role->add_cap('read_' . $type);
                $role->add_cap('delete_' . $type);
                $role->add_cap('edit_' . $type . 's');
                $role->add_cap('edit_others_' . $type . 's');
                $role->add_cap('publish_' . $type . 's');
                $role->add_cap('delete_' . $type . 's');
                $role->add_cap('delete_published_' . $type . 's');
                $role->add_cap('delete_others_' . $type . 's');
                $role->add_cap('edit_published_' . $type . 's');
            }
        }

        // Guide Administrator
        $postTypes = array('guide', 'location', 'interactive_guide');
        $role = get_role('guide_administrator');
        if ($role) {
            foreach ($postTypes as $key => $type) {
                $role->add_cap('edit_' . $type);
                $role->add_cap('read_' . $type);
                $role->add_cap('delete_' . $type);
                $role->add_cap('edit_' . $type . 's');
                $role->add_cap('edit_others_' . $type . 's');
                $role->add_cap('publish_' . $type . 's');
                $role->add_cap('read_private_' . $type . 's');
                $role->add_cap('delete_' . $type . 's');
                $role->add_cap('delete_private_' . $type . 's');
                $role->add_cap('delete_published_' . $type . 's');
                $role->add_cap('delete_others_' . $type . 's');
                $role->add_cap('edit_private_' . $type . 's');
                $role->add_cap('edit_published_' . $type . 's');
            }
        }

        // Guide Editor
        $postTypes = array('guide', 'location', 'interactive_guide');
        $role = get_role('guide_editor');
        if ($role) {
            foreach ($postTypes as $key => $type) {
                $role->add_cap('edit_' . $type);
                $role->add_cap('read_' . $type);
                $role->add_cap('delete_' . $type);
                $role->add_cap('edit_' . $type . 's');
                $role->add_cap('edit_others_' . $type . 's');
                $role->add_cap('publish_' . $type . 's');
                $role->add_cap('delete_' . $type . 's');
                $role->add_cap('delete_published_' . $type . 's');
                $role->add_cap('delete_others_' . $type . 's');
                $role->add_cap('edit_published_' . $type . 's');
            }
        }
    }
}
