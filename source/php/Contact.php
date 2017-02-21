<?php

namespace HbgEventImporter;

use \HbgEventImporter\Helper\DataCleaner as DataCleaner;

class Contact extends \HbgEventImporter\Entity\PostManager
{
    public $post_type = 'contact';

    /**
     * Stuff to do before save
     * @return void
     */
    public function beforeSave()
    {
        // Format phone number
        $this->phone_number = DataCleaner::phoneNumber($this->phone_number);

        // Validate email
        $this->email = DataCleaner::email($this->email);

        $this->post_title = DataCleaner::string($this->post_title);
        $this->name = DataCleaner::string($this->name);
        $this->_event_manager_uid = DataCleaner::string($this->_event_manager_uid);
    }

    /**
     * Stuff to do after save
     * @return void
     */
    public function afterSave()
    {
        $this->saveGroups();
        return true;
    }
    /**
     * Saves publishing groups as user_groups taxonomy terms
     * @return void
     */
    public function saveGroups()
    {
        wp_set_object_terms($this->ID, $this->user_groups, 'user_groups', true);
    }
}
