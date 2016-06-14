<?php

namespace HbgEventImporter;

class Event extends \HbgEventImporter\Entity\PostManager
{
    public $post_type = 'event';

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
