<?php

namespace HbgEventImporter\Admin;

class Options
{
    public function __construct()
    {
        if (function_exists('acf_add_options_sub_page')) {
            acf_add_options_sub_page(array(
                'page_title'    => _x('Event manager options', 'ACF', 'event-manager'),
                'menu_title'    => _x('Options', 'Event manager options', 'event-manager'),
                'menu_slug'     => 'acf-options-options',
                'parent_slug'   => 'edit.php?post_type=event',
                'capability'    => 'install_themes'
            ));
        }
    }
}
