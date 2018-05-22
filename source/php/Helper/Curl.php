<?php

namespace HbgEventImporter\Helper;

class Curl
{
    /**
     * Curl request
     * @param  string $type        Request type
     * @param  string $url         Request url
     * @param  array $data         Request data
     * @param  string $contentType Content type
     * @param  array $headers      Request headers
     * @return string              The request response
     */

    public static function request($type, $url, $userPwd = false, $data = null, $contentType = 'json', $headers = null)
    {

        //Arguments are stored here
        $arguments = null;

        switch (strtoupper($type)) {
            /**
             * Method: GET
             */
            case 'GET':
                // Append $data as querystring to $url
                if (is_array($data)) {
                    $url .= '?' . http_build_query($data);
                }

                // Set curl options for GET
                $arguments = array(
                    CURLOPT_RETURNTRANSFER      => true,
                    CURLOPT_HEADER              => false,
                    CURLOPT_FOLLOWLOCATION      => true,
                    CURLOPT_SSL_VERIFYPEER      => false,
                    CURLOPT_SSL_VERIFYHOST      => false,
                    CURLOPT_URL                 => $url,
                    CURLOPT_USERPWD             => $userPwd,
                    CURLOPT_CONNECTTIMEOUT_MS  => 1500
                );

                break;

            /**
             * Method: POST
             */
            case 'POST':
                // Set curl options for POST
                $arguments = array(
                    CURLOPT_RETURNTRANSFER      => 1,
                    CURLOPT_URL                 => $url,
                    CURLOPT_USERPWD             => $userPwd,
                    CURLOPT_POST                => 1,
                    CURLOPT_HEADER              => false,
                    CURLOPT_CONNECTTIMEOUT_MS   => 3000
                );

                if (in_array($contentType, array("json", "jsonp"))) {
                    $arguments[CURLOPT_POSTFIELDS] = json_encode($data);
                } else {
                    $arguments[CURLOPT_POSTFIELDS] = http_build_query($data) ;
                }

                break;
        }

        /**
         * Set up headers if given
         */
        if ($headers) {
            $arguments[CURLOPT_HTTPHEADER] = $headers;
        }

        /**
         * Do the actual curl
         */
        $ch = curl_init();
        curl_setopt_array($ch, $arguments);
        $httpcode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
        $response = curl_exec($ch);
        curl_close($ch);

        /**
         * Return the response
         */
        return $response;
    }
}
