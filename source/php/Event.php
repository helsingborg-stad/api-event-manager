<?php

namespace HbgEventImporter;

use \HbgEventImporter\Helper\DataCleaner as DataCleaner;

class Event extends \HbgEventImporter\Entity\PostManager
{
    public $post_type = 'event';

    /**
     * Stuff to do before save
     * @return void
     */
    public function beforeSave()
    {
        // Format phone number
        $this->organizer_phone = DataCleaner::phoneNumber($this->organizer_phone);
        $this->booking_phone = DataCleaner::phoneNumber($this->booking_phone);
    }

    /**
     * Stuff to do after save
     * @return void
     */
    public function afterSave()
    {
        $this->saveCategories();
    }

    /**
     * Saves categories as event-categories taxonomy terms
     * @return void
     */
    public function saveCategories()
    {
        wp_set_object_terms($this->ID, $this->categories, 'event-categories', true);
    }
}
