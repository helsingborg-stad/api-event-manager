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

        // Validate email
        $this->organizer_email = DataCleaner::email($this->organizer_email);

        // clean strings
        $this->post_title = !is_string($this->post_title) ? $this->post_title : DataCleaner::string($this->post_title);
        $this->post_content = !is_string($this->post_content) ? $this->post_content : DataCleaner::string($this->post_content);
        $this->uniqueId = !is_string($this->uniqueId) ? $this->uniqueId : DataCleaner::string($this->uniqueId);
        $this->_event_manager_uid = !is_string($this->_event_manager_uid) ? $this->_event_manager_uid : DataCleaner::string($this->_event_manager_uid);
        $this->status = !is_string($this->status) ? $this->status : DataCleaner::string($this->status);
        $this->alternate_name = !is_string($this->alternate_name) ? $this->alternate_name : DataCleaner::string($this->alternate_name);
        $this->event_link = !is_string($this->event_link) ? $this->event_link : DataCleaner::string($this->event_link);
        $this->coorganizer = !is_string($this->coorganizer) ? $this->coorganizer : DataCleaner::string($this->coorganizer);
        $this->booking_link = !is_string($this->booking_link) ? $this->booking_link : DataCleaner::string($this->booking_link);
        $this->age_restriction = !is_string($this->age_restriction) ? $this->age_restriction : DataCleaner::string($this->age_restriction);
        $this->price_information = !is_string($this->price_information) ? $this->price_information : DataCleaner::string($this->price_information);
        $this->price_adult = !is_string($this->price_adult) ? $this->price_adult : DataCleaner::string($this->price_adult);
        $this->price_children = !is_string($this->price_children) ? $this->price_children : DataCleaner::string($this->price_children);
        $this->image = !is_string($this->image) ? $this->image : DataCleaner::string($this->image);
    }

    /**
     * Stuff to do after save
     * @return bool ,used if post got removed or not
     */
    public function afterSave()
    {
        $this->saveCategories();
        $this->saveOccasions();
        return true;
    }

    /**
     * Saves categories as event-categories taxonomy terms
     * @return void
     */
    public function saveCategories()
    {
        wp_set_object_terms($this->ID, $this->categories, 'event-categories', true);
    }

    /**
     * Saves occasions to the occasions repeater
     * @return void
     */
    public function saveOccasions()
    {
        foreach($this->occasions as $o) {
            $this->extractEventOccasion($o['start_date']);
        }
        update_field('field_5761106783967', $this->occasions, $this->ID);
    }

    public function extractEventOccasion($startDate)
    {
        global $wpdb;
        $eventId = $this->ID;
        $timestamp = strtotime($startDate);
        if($timestamp <= 0)
            return;
        //var_dump($timestamp);
        $testQuery = $wpdb->prepare("SELECT ID,event,timestamp FROM event_occasions WHERE event = %d AND timestamp = %d", $eventId, $timestamp);
        $existing = $wpdb->get_results($testQuery);
        //var_dump($existing);
        $resultString = '';
        if(empty($existing))
        {
            $wpdb->insert('event_occasions', array('event' => $eventId, 'timestamp' => $timestamp));
            $resultString .= "New event occasions inserted with event id: " . $eventId . ', and timestamp: ' . $timestamp . "\n";
        }
        else
            $resultString .= "Already exists! Event: " . $existing[0]->event . ', timestamp: ' . $existing[0]->timestamp . "\n";
    }
}
