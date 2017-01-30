<?php

namespace HbgEventImporter;

abstract class Parser
{
    protected $url;
    protected $apiKeys;
    protected $cbisLocation;
    protected $nrOfNewEvents;
    protected $nrOfNewLocations;
    protected $nrOfNewContacts;
    private $db;

    /**
     * Holds all titles of existing locations, contacts and events in wordpress
     * @var array
     */
    protected $levenshteinTitles = array('location' => array(), 'contact' => array(), 'event' => array());

    public function __construct($url, $apiKeys = null, $cbisLocation = null)
    {

        // Class specific wpdb
        global $wpdb;
        $this->db = $wpdb;

        // Max excec time
        ini_set('max_execution_time', 60*5);

        // Setup vars
        $this->url              = $url;
        $this->apiKeys          = $apiKeys;
        $this->cbisLocation     = $cbisLocation;
        $this->nrOfNewEvents    = 0;
        $this->nrOfNewLocations = 0;
        $this->nrOfNewContacts  = 0;

        // Run import
        $this->start();
    }

    /**
     * Collecting titles of existing events, locations and contacts
     * @return void
     */
    public function collectDataForLevenshtein()
    {
        $types = (array) apply_filters('event/parser/common/levenshtein/post_types', array('event', 'location', 'contact'));

        foreach ((array) $types as $type) {
            $allOfCertainType = $this->db->get_results(
                $this->db->prepare("SELECT ID, post_title FROM " . $this->db->posts . " WHERE (post_status = %s OR post_status = %s) AND post_type = %s", 'publish', 'draft', $type)
            );

            foreach ((array) $allOfCertainType as $post) {
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
        foreach ($this->levenshteinTitles[$postType] as $title) {
            if ($this->isSimilarEnough(trim(html_entity_decode($postTitle)), $title['post_title'], $postType == 'location' ? 1 : 3)) {
                return $title['ID'];
            }
        }
        return null;
    }

    /**
     * Check if the new title are similar enough with existing one
     * @return boolean
     */
    public function isSimilarEnough($newTitle, $existingTitle, $threshold)
    {
        $newTitle       = strtolower($newTitle);
        $existingTitle  = strtolower($existingTitle);

        if (levenshtein($newTitle, $existingTitle) <= $threshold) {
            return true;
        }
        return false;
    }

    /**
     * Get statistics for imported dataset
     * @return array
     */

    public function getCreatedData()
    {
        return array(
                'events' => $this->nrOfNewEvents,
                'locations' => $this->nrOfNewLocations,
                'contacts' => $this->nrOfNewContacts
            );
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

    /**
     * Removes spaces and special characters from string
     * @param  string $string string to clean
     * @return string
     */
    public function cleanString($string)
    {
        $string = str_replace(' ', '-', $string);

        return preg_replace('/[^A-Za-z0-9\-]/', '', $string);
    }
}
