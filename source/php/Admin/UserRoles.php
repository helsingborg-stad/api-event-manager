<?php

namespace HbgEventImporter\Admin;

/**
* Add user roles and capabilities
*/
class UserRoles
{
	public function __construct()
	{
        add_action('admin_init', array($this, 'addCapabilities'));
        add_action('pre_get_users', array($this, 'filterUserList'));
        add_filter('editable_roles', array($this, 'filterEditableRoles'));
        add_filter('views_users', array($this, 'hideUserRoleQuicklinks'));
        add_action('admin_menu', array($this, 'my_remove_menu_pages'));
	}

	/**
	 * Hide options for non admins
	 * @return void
	 */
	public function my_remove_menu_pages() {
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
    public function hideUserRoleQuicklinks($views) {
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
    public function filterUserList( $query ) {
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
                $query->set('meta_query', $meta_query );
                $query->set('role__not_in', array('administrator'));
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
    public static function removeUserRoles()
    {
    	$roles = array('guide_administrator', 'event_contributor', 'guide_editor');
    	foreach ($roles as $role) {
    		if (get_role($role)) {
    			remove_role($role);
    		}
    	}
    }

    /**
     * Add user capabilities to custom post types
     */
	public function addCapabilities()
	{
	    // Administrator
	    $postTypes = array('event', 'location', 'sponsor', 'package', 'membership-card', 'guide', 'organizer');
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
	    $postTypes = array('event', 'location', 'sponsor', 'package', 'membership-card', 'organizer');
	    $role = get_role('event_administrator');
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
	        $role->add_cap('edit_published_' . $type );
	    }
        $role->add_cap('edit_users');
        $role->add_cap('list_users');
        $role->add_cap('promote_users');
        $role->add_cap('create_users');
        $role->add_cap('add_users');
        $role->add_cap('delete_users');

	    // Editor
	    $postTypes = array('event', 'location', 'sponsor', 'package', 'membership-card', 'guide', 'organizer');
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
	    $postTypes = array('event', 'location', 'sponsor', 'package', 'membership-card', 'organizer');
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
	    $postTypes = array('guide', 'location');
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
	    $postTypes = array('guide', 'location');
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
