<?php

namespace HbgEventImporter\Helper;

class Address
{
    /**
     * Get coordinates from address
     * @param  string $address Address
     * @param  boolean $type   is true if address exists
     * @return array           Lat and long
     */
    public static function gmapsGetAddressComponents($address, $type)
    {
        if (empty(get_option('options_google_geocode_api_key')) || empty($address) || $address == 'null') {
            return false;
        }

        // If $type == false, address is missing and uses the Google Places API instead.
        if ($type) {
            $url = 'https://maps.googleapis.com/maps/api/geocode/json?address=' . urlencode($address) . '&key=' . get_option('options_google_geocode_api_key');
            $data = json_decode(file_get_contents($url));
        } else {
            $url = 'https://maps.googleapis.com/maps/api/place/textsearch/json?query=' . urlencode($address) . '&key=' . get_option('options_google_geocode_api_key');
            $data = json_decode(file_get_contents($url));
            if (isset($data->status) && $data->status == 'OK') {
                $place_id = $data->results[0]->place_id;
                $url = 'https://maps.googleapis.com/maps/api/geocode/json?place_id=' . $place_id . '&key=' . get_option('options_google_geocode_api_key');
                $data = json_decode(file_get_contents($url));
            } else {
                return false;
            }
        }

        if (isset($data->status) && $data->status == 'OK') {
            return $data->results[0];
        }

        return false;
    }

    /**
     * Get coordinates from address
     * @param  string $address Address
     * @return array          Lat and long
     */
    public static function gmapsGetAddressByCoordinates($lat, $lng)
    {
        $lat = str_replace(',', '.', $lat);
        $lng = str_replace(',', '.', $lng);
        $coordinates = $lat . ',' . $lng;

        $url = 'http://maps.googleapis.com/maps/api/geocode/json?latlng=' . urlencode($coordinates);
        $data = json_decode(file_get_contents($url));

        if (isset($data->status) && $data->status == 'OK') {
            return (object)array(
                'street' => $data->results[0]->address_components[1]->long_name . ' ' . $data->results[0]->address_components[0]->long_name,
                'city' => (isset($data->results[0]->address_components[3]->long_name)) ? $data->results[0]->address_components[3]->long_name : null,
                'postalcode' => (isset($data->results[0]->address_components[6]->long_name)) ? $data->results[0]->address_components[6]->long_name : null,
                'country' => (isset($data->results[0]->address_components[5]->long_name)) ? $data->results[0]->address_components[5]->long_name : null,
                'formatted_address' => $data->results[0]->formatted_address
            );
        }

        return false;
    }
}
