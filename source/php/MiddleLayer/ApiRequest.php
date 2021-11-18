<?php

namespace HbgEventImporter\MiddleLayer;

class ApiRequest
{
    public $apiUrl;
    public $apiKey;
    public $endpoint;
    public $isCdnSyncEnabled;

    public function __construct($endpoint)
    {
        $this->isCdnSyncEnabled = get_option('options_enable_cdn_api_sync');
        $this->apiUrl = get_option('options_cdn_api_url');
        $this->apiKey = get_option('options_cdn_api_key');
        $this->endpoint = $endpoint;
    }

    public function post($body)
    {
        $url = $this->apiUrl . '/' . $this->endpoint;
        $args = [
          'body' => $body,
          'headers' => [
            'Content-Type' => 'application/json',
            'Authorization' => $this->apiKey,
          ],
          'sslverify' => defined('DEV_MODE') && DEV_MODE == true ? false : true,
        ];
        $response = wp_remote_post($url, $args);

        if (is_wp_error($response)) {
            $errorMessage = $response->get_error_message();
            error_log("Guide API POST \"$this->endpoint\" error: $errorMessage");
        }
    }

    public function delete($id)
    {
        $url = $this->apiUrl . '/' . $this->endpoint . '/' . $id;
        $args = array(
          'headers' => [
            'Authorization' => $this->apiKey,
          ],
          'method' => 'DELETE',
          'sslverify' => defined('DEV_MODE') && DEV_MODE == true ? false : true,
        );
        $response = wp_remote_request($url, $args);

        if (is_wp_error($response)) {
            $errorMessage = $response->get_error_message();
            error_log("Guide API DELETE \"$this->endpoint/$id\" error: $errorMessage");
        }
    }
}
