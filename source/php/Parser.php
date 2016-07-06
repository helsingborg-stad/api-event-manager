<?php

namespace HbgEventImporter;

abstract class Parser
{
    protected $url;

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
            $allOfCertainType = $wpdb->get_results("SELECT ID,post_title FROM " . $wpdb->posts . " WHERE post_status = 'publish' AND post_type = '" . $type . "'");
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
            if($this->isSimilarEnough($postTitle, $title['post_title'], $postType == 'location' ? 0 : 3))
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
        $steps = levenshtein($newTitle, $existingTitle);
        if($steps <= $threshold)
            return true;
        return false;
    }

    public function __construct($url)
    {
        ini_set('max_execution_time', 300);

        $this->url = $url;
        $this->start();
        $this->done();
    }

    /**
     * Used to start the parsing
     */
    abstract public function start();

    public function done()
    {
        echo 'Parser done.';
        echo '<script>location.href = "' . admin_url('edit.php?post_type=event&msg=import-complete') . '";</script>';
    }
}
