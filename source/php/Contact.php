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
        $this->phoneNumber = DataCleaner::phoneNumber($this->phoneNumber);
    }
}
