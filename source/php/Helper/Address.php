<?php

namespace HbgEventImporter\Helper;

class Address
{
    /**
     * Get coordinates from address
     * @param  string $address Address
     * @return array          Lat and long
     */
    public static function gmapsGetAddressComponents($address)
    {
        if (empty(get_option('options_google_geocode_api_key'))) {
            return false;
        }

        $url = 'https://maps.googleapis.com/maps/api/geocode/json?address=' . urlencode($address) . '&key=' . get_option('options_google_geocode_api_key');
        $data = json_decode(file_get_contents($url));

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
                'postalcode' => (isset($data->results[0]->address_components[6]->long_name)) ? $data->results[0]->address_components[6]->long_name : null
            );
        }

        return false;
    }
}
