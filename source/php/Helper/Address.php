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
        $url = 'https://maps.googleapis.com/maps/api/geocode/json?address=' . urlencode($address) . '&key=AIzaSyAK4m-Yqi12k0CsfCwc5S0av_JK9gJ-4uE';
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
