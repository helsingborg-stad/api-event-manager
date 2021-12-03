<?php

namespace HbgEventImporter;

abstract class Parser
{
    private $db;

    protected $url;
    protected $apiKeys;
    protected $cbisLocation;

    /**
     * Holds all titles of existing locations, organizers and events in wordpress
     * @var array
     */
    protected $levenshteinTitles = array(
        'location' => array(),
        'event' => array(),
        'organizer' => array()
    );

    public function __construct($url, $apiKeys = null, $cbisLocation = null)
    {

        // Class specific wpdb
        global $wpdb;
        $this->db = $wpdb;

        // Max excec time
        ini_set('max_execution_time', 60*5);

        // Setup vars
        $this->url               = $url;
        $this->apiKeys           = $apiKeys;
        $this->cbisLocation      = $cbisLocation;

        // Run import
        $this->start();
    }

    /**
     * Used to start the parsing
     */
    abstract public function start();

    /**
     * Collecting titles of existing events, locations and organizers
     * @return void
     */
    public function collectDataForLevenshtein()
    {
        $types = (array) apply_filters(
            'event/parser/common/levenshtein/post_types',
            array(
                'event',
                'location',
                'organizer'
            )
        );

        foreach ($types as $type) {
            $allOfCertainType = $this->db->get_results(
                $this->db->prepare("SELECT ID, post_title FROM " . $this->db->posts . " WHERE (post_status = %s OR post_status = %s) AND post_type = %s", 'publish', 'draft', $type)
            );

            if (is_array($allOfCertainType)) {
                foreach ($allOfCertainType as $post) {
                    $this->levenshteinTitles[$type][] = array('ID' => $post->ID, 'post_title' => $post->post_title, 'occurred' => false);
                }
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
            if ($this->isSimilarEnough(
                trim(html_entity_decode($postTitle)), 
                trim(html_entity_decode($title['post_title'])), 
                $postType == 'location' ? 1 : 3)) {
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
