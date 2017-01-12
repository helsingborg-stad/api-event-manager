<?php

namespace HbgEventImporter;

abstract class Parser
{
    protected $url;
    protected $apiKeys;
    protected $nrOfNewEvents;
    protected $nrOfNewLocations;
    protected $nrOfNewContacts;

    /**
     * Holds all titles of existing locations, contacts and events in wordpress
     * @var array
     */
    protected $levenshteinTitles = array('location' => array(), 'contact' => array(), 'event' => array());

    /**
     * Collecting titles of existing events, locations and contacts
     * @return void
     */
    public function collectDataForLevenshtein()
    {
        global $wpdb;
        $types = array('event', 'location', 'contact');

        foreach($types as $type) {
            $sql = $wpdb->prepare("SELECT ID,post_title FROM " . $wpdb->posts . " WHERE (post_status = %s OR post_status = %s) AND post_type = %s", 'publish', 'draft', $type);
            $allOfCertainType = $wpdb->get_results($sql);
            foreach ($allOfCertainType as $post) {
                $this->levenshteinTitles[$type][] = array('ID' => $post->ID, 'post_title' => $post->post_title);
            }
        }
    }

    /**
     * See if title for post already exists or something that are really similar, using levenshtein
     * @return boolean
     */
    public function checkIfPostExists($postType, $postTitle)
    {
        foreach($this->levenshteinTitles[$postType] as $title) {
            if($this->isSimilarEnough(trim(html_entity_decode($postTitle)), $title['post_title'], $postType == 'location' ? 1 : 3))
                return $title['ID'];
        }
        return null;
    }

    /**
     * Check if the new title are similar enough with existing one
     * @return boolean
     */
    public function isSimilarEnough($newTitle, $existingTitle, $threshold)
    {
        $forTest1 = strtolower($newTitle);
        $forTest2 = strtolower($existingTitle);
        $steps = levenshtein($forTest1, $forTest2);
        if($steps <= $threshold)
            return true;
        return false;
    }

    public function __construct($url, $apiKeys = null)
    {
        ini_set('max_execution_time', 300);

        $this->url = $url;
        $this->apiKeys = $apiKeys;
        $this->nrOfNewEvents = 0;
        $this->nrOfNewLocations = 0;
        $this->nrOfNewContacts = 0;
        $this->start();
        //$this->done();
    }

    public function getCreatedData()
    {
        return array('events' => $this->nrOfNewEvents, 'locations' => $this->nrOfNewLocations, 'contacts' => $this->nrOfNewContacts);
    }

    /**
     * Used to start the parsing
     */
    abstract public function start();

    public function done()
    {
        echo __('Parser done.', 'event-manager');
        echo '<script>location.href = "' . admin_url('edit.php?post_type=event&msg=import-complete') . '";</script>';
    }
}
