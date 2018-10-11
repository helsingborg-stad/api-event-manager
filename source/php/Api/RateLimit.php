<?php

namespace HbgEventImporter\Api;

class RateLimit
{

    private $ip             = null;
    private $rateLimit      = 500; //Number of requests allowed in timeframe
    private $timeFrame      = 60; //In minutes
    private $cacheKey       = "event-importer-rate-limit";
    private $cacheTtl       = 60*60*12; //Auto clear cache after 12 hours
    private $persistentBan  = array("91.106.193.250");

    public function __construct()
    {

        //Set remote ip
        $this->ip = $_SERVER['REMOTE_ADDR'];

        //Regsiter this request
        add_action('rest_api_init', array($this, 'registerRequest'), 1);

        //Check if banned
        add_action('rest_api_init', array($this, 'bannedRequest'), 3);
    }

    /**
     * Register the request
     * @return  boolean
     * @version 0.16.3
     */
    public function registerRequest()
    {
        return wp_cache_set($this->cacheKey, array_merge($this->getRegisteredRequests(), array(time())), $this->ip, $this->cacheTtl);
    }

    /**
     * Get registered requests by ip-adress
     * @return  array
     * @version 0.16.3
     */
    public function getRegisteredRequests() : array
    {
        if (is_array($response = wp_cache_get($this->cacheKey, $this->ip))) {
            return $this->filterRequestItems($response);
        }
        return array();
    }

    /**
     * Remove old entrys (not valid anymore)
     * @return  boolean
     * @version 0.16.3
     */
    private function filterRequestItems($itemArray) : array
    {
        return array_filter($itemArray, function ($item) {
            if ($item < (time() - ($this->timeFrame * 60))) {
                return false;
            }
            return true;
        });
    }

    /**
     * Send ban notices (block furhter access)
     * @return  void
     * @version 0.16.3
     */
    public function bannedRequest()
    {

        //Persistant ban
        if (in_array($this->ip, $this->persistentBan)) {

            //Log this
            $this->logBlockedAttempt("a persistant ban");

            wp_send_json(array(
                'code' => 'banned_remote_ip',
                'message' => 'You have been banned due to many malformed requests. This ban is persistent and can only be removed manually by the administrator.',
                'data' => array('status' => 429)
            ), 429);
            exit;
        }

        //Temporary ban (rate limit)
        if (($numberOfRequests = count($this->getRegisteredRequests())) > $this->rateLimit) {

            //Log this
            $this->logBlockedAttempt("rate limit exceeded");

            //Send json
            wp_send_json(array(
                'code' => 'rate_limit_exceeded',
                'message' => 'You have been temporarily banned due to many requests.',
                'data' => array(
                    'status' => 429,
                    'numberOfRequests' => $numberOfRequests
                )
            ), 429);
            exit;
        }
    }

    /**
     * Log block error
     * @return  void
     * @version 0.16.3
     */
    public function logBlockedAttempt($reason)
    {
        error_log("Event manager api blocked a request due too " . $reason . ".");
    }
}
