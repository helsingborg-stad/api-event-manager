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

        $this->post_title = !is_string($this->post_title) ? $this->post_title : DataCleaner::string($this->post_title);
        $this->name = !is_string($this->name) ? $this->name : DataCleaner::string($this->name);
        $this->_event_manager_uid = !is_string($this->_event_manager_uid) ? $this->_event_manager_uid : DataCleaner::string($this->_event_manager_uid);
    }
}
