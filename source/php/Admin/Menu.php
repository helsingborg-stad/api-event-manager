<?php

namespace HbgEventImporter\Admin;

/**
 * Cleaning up the wordpress api
 */

class Menu
{
    public function __construct()
    {
        add_action('admin_menu', array($this, 'removeAdminMenuItems'), 100);
    }

    public function removeAdminMenuItems()
    {
        remove_menu_page('index.php');                      //Dashboard
        remove_menu_page('edit.php');                       //Posts
        remove_menu_page('edit.php?post_type=page');        //Pages
        remove_menu_page('edit-comments.php');              //Comments
        remove_menu_page('themes.php');                     //Appearance
        remove_menu_page('tools.php');                      //Tools
        remove_menu_page('admin.php?page=theme-settings');  //Theme options
    }
}
