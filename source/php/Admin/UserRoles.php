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
	}

    /**
     * Create custom user roles
     * @return void
     */
    public static function createUserRoles()
    {
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
	    $postTypes = array('event', 'location', 'contact', 'sponsor', 'package', 'membership-card', 'guide', 'organizer');
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

	    // Editor
	    $postTypes = array('event', 'location', 'contact', 'sponsor', 'package', 'membership-card', 'guide', 'organizer');
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
	    $postTypes = array('event', 'location', 'contact', 'sponsor', 'package', 'membership-card', 'organizer');
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
