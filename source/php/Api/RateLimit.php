<?php
/**
 * Limiting number of allowed requests.
 */

namespace HbgEventImporter\Api;

class RateLimit
{

    private $_ip             = null;
    private $_rateLimit      = 500; //Number of requests allowed in _timeFrame
    private $_timeFrame      = 60; //In minutes
    private $_cacheKey       = "event-importer-rate-limit";
    private $_cacheTtl       = 60*60*12; //Auto clear cache after 12 hours
    private $_persistentBan  = array("91.106.193.250");

    /**
     * Hook to WordPress. Gather remote ip.
     *
     * @return  boolean
     * @version 0.16.3
     */
    public function __construct()
    {

        //Set remote ip
        $this->_ip = $_SERVER['REMOTE_ADDR'];

        //Regsiter this request
        add_action('rest_api_init', array($this, 'registerRequest'), 1);

        //Check if banned
        add_action('rest_api_init', array($this, 'bannedRequest'), 2);
    }

    /**
     * Register the request
     *
     * @return  boolean
     * @version 0.16.3
     */
    public function registerRequest()
    {
        return wp_cache_set(
            $this->_cacheKey,
            array_merge($this->getRegisteredRequests(), array(time())),
            $this->_ip, $this->_cacheTtl
        );
    }

    /**
     * Get registered requests by ip-adress
     *
     * @return  array
     * @version 0.16.3
     */
    public function getRegisteredRequests() : array
    {
        if (is_array($response = wp_cache_get($this->_cacheKey, $this->_ip))) {
            return $this->_filterRequestItems($response);
        }
        return array();
    }

    /**
     * Remove old entrys (not valid anymore)
     *
     * @param array $itemArray Array of previous items
     *
     * @return  boolean
     * @version 0.16.3
     */
    private function _filterRequestItems($itemArray) : array
    {
        return array_filter(
            $itemArray, function ($item) {
                if ($item < (time() - ($this->_timeFrame * 60))) {
                    return false;
                }
                return true;
            }
        );
    }

    /**
     * Send ban notices (block furhter access)
     *
     * @return  void
     * @version 0.16.3
     */
    public function bannedRequest()
    {

        //Persistant ban
        if (in_array($this->_ip, $this->_persistentBan)) {

            //Log this
            $this->logBlockedAttempt("a persistant ban");

            wp_send_json(
                array(
                    'code' => 'banned_remote_ip',
                    'message' => 'You have been banned due to many malformed requests. This ban is persistent and can only be removed manually by the administrator.',
                    'data' => array('status' => 429)
                ), 429
            );

        }

        //Temporary ban (rate limit)
        if (($numberOfRequests = count($this->getRegisteredRequests())) > $this->_rateLimit) {

            //Log this
            $this->logBlockedAttempt("rate limit exceeded");

            //Send json
            wp_send_json(
                array(
                    'code' => 'rate_limit_exceeded',
                    'message' => 'You have been temporarily banned due to many requests.',
                    'data' => array(
                        'status' => 429,
                        'numberOfRequests' => $numberOfRequests
                    )
                ), 429
            );
        }
    }

    /**
     * Log block error
     *
     * @param string $reason [String with reason for ban.]
     *
     * @return  void
     * @version 0.16.3
     */
    public function logBlockedAttempt($reason)
    {
        error_log("Event manager api blocked a request due too " . $reason . ".");
    }
}
