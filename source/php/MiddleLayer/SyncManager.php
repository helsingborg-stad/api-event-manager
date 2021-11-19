<?php

namespace HbgEventImporter\MiddleLayer;

class SyncManager
{
    public $apiUrl;
    public $apiKey;
    public $singularName;
    public $pluralName;
    public $isCdnSyncEnabled;

    public function __construct($singularName, $pluralName)
    {
        $this->isCdnSyncEnabled = get_option('options_enable_cdn_api_sync');
        $this->apiUrl = get_option('options_cdn_api_url');
        $this->apiKey = get_option('options_cdn_api_key');
        $this->singularName = $singularName;
        $this->pluralName = $pluralName;
    }

    public function deleteItem($term)
    {
        $this->delete($term);
    }

    public function saveItem($id)
    {
        $data = $this->getRestResponse($id);
        if (empty($data)) {
            return;
        }
        $json = wp_json_encode($data);
        $this->post($json);
    }

    public function saveEmbeddedItem($id)
    {
        $data = $this->getRestResponse($id, true);
        if (empty($data)) {
            return;
        }
        $json = wp_json_encode($data);
        $this->post($json);
    }

    public function post($body)
    {
        $url = $this->apiUrl . '/' . $this->pluralName;
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
            error_log("Middle Layer API POST \"$this->pluralName\" error: $errorMessage");
        }
    }

    public function delete($id)
    {
        $url = $this->apiUrl . '/' . $this->pluralName . '/' . $id;
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
            error_log("Middle Layer API DELETE \"$this->pluralName/$id\" error: $errorMessage");
        }
    }

    public function getRestResponse($id, $embedded = false)
    {
        $request = new \WP_REST_Request('GET', '/wp/v2/' . $this->singularName . '/' . $id);
        $response = rest_do_request($request);

        if ($response->is_error()) {
            $error = $response->as_error();
            $errorMessage = $error->get_error_message();
            $errorData = $error->get_error_data();
            $status = isset($errorData['status']) ? $errorData['status'] : 500;
            error_log("Get REST response \"$this->singularName/$id\" error: ($status) $errorMessage");
            return;
        }

        $server = rest_get_server();
        $data = $server->response_to_data($response, $embedded);

        return $data;
    }
}
