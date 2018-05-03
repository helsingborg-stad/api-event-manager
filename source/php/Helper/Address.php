<?php

namespace HbgEventImporter\Helper;

class Address
{
    /**
     * Get cooridnates and complete address from address or place name
     * @param  string $address Address
     * @param  boolean $type   is true if address exists, is false if only place name exist
     * @return object          Address components
     */
    public static function gmapsGetAddressComponents($address, $type = true)
    {
        if (empty(get_option('options_google_geocode_api_key')) || empty($address) || $address == 'null') {
            return false;
        }

        // If $type equals false, address is missing will use Google Places API instead.
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

        // Save address components to address object
        if (isset($data->status) && $data->status == 'OK') {
            $addressArray = (object)array();
            $street = '';
            $streetNumber = '';
            $addressArray->street = '';

            foreach ($data->results[0]->address_components as $key => $component) {
                $addressArray->postalcode = ($component->types[0] == 'postal_code') ? $component->long_name : '';

                if (!empty($component->types[0] == 'locality')) {
                    $addressArray->city = $component->long_name;
                } elseif ($component->types[0] == 'postal_town') {
                    $addressArray->city = $component->long_name;
                }

                if ($component->types[0] == 'country') {
                    $addressArray->country = $component->long_name;
                }

                if ($component->types[0] == 'route') {
                    $street = $component->long_name;
                }

                if ($component->types[0] == 'street_number') {
                    $streetNumber = $component->long_name;
                }
            }

            if (!empty($street)) {
                if (!empty($streetNumber)) {
                    $street .= ' ' . $streetNumber;
                }

                $addressArray->street = $street;
            }

            $addressArray->formatted_address = $data->results[0]->formatted_address;
            $addressArray->latitude = $data->results[0]->geometry->location->lat;
            $addressArray->longitude = $data->results[0]->geometry->location->lng;

            return $addressArray;
        }

        return false;
    }

    /**
     * Get address from coordinates
     * @param  string $lat latitude coordinate
     * @param  string $lng longitude coordinate
     * @return object      address components
     */
    public static function gmapsGetAddressByCoordinates($lat, $lng)
    {
        $lat = str_replace(',', '.', $lat);
        $lng = str_replace(',', '.', $lng);
        $coordinates = $lat . ',' . $lng;

        $url = 'http://maps.googleapis.com/maps/api/geocode/json?latlng=' . urlencode($coordinates);
        $data = json_decode(file_get_contents($url));

        // Save address components to address object
        if (isset($data->status) && $data->status == 'OK') {
            $addressArray = (object)array();
            $street = '';
            $streetNumber = '';

            foreach ($data->results[0]->address_components as $key => $component) {
                $addressArray->postalcode = (!empty($component->types[0] == 'postal_code')) ? $component->long_name : '';

                if (!empty($component->types[0] == 'locality')) {
                    $addressArray->city = $component->long_name;
                } elseif ($component->types[0] == 'postal_town') {
                    $addressArray->city = $component->long_name;
                }

                if ($component->types[0] == 'country') {
                    $addressArray->country = $component->long_name;
                }

                if ($component->types[0] == 'route') {
                    $street = $component->long_name;
                }

                if ($component->types[0] == 'street_number') {
                    $streetNumber = $component->long_name;
                }
            }

            if (!empty($street)) {
                if (!empty($streetNumber)) {
                    $street .= ' ' . $streetNumber;
                }

                $addressArray->street = $street;
            }

            $addressArray->formatted_address = $data->results[0]->formatted_address;
            return $addressArray;
        }

        return false;
    }

    /**
     * Get nearby locations within given distance
     * @param  string       $lat       latitude
     * @param  string       $lng       longitude
     * @param  int/float    $distance  radius distance in km
     * @return array with locations
     */
    public static function getNearbyLocations($lat, $lng, $distance = 0)
    {
        global $wpdb;

        // Radius of the earth in kilometers.
        $earth_radius = 6371;
        $sql = $wpdb->prepare(
            "SELECT DISTINCT
                latitude.post_id,
                post.post_title,
                latitude.meta_value as lat,
                longitude.meta_value as lng,
                (%s * ACOS(
                    COS(RADIANS( %s )) * COS(RADIANS(latitude.meta_value)) * COS(
                    RADIANS(longitude.meta_value) - RADIANS( %s )
                    ) + SIN(RADIANS( %s )) * SIN(RADIANS(latitude.meta_value))
                )) AS distance
            FROM $wpdb->posts post
            INNER JOIN $wpdb->postmeta latitude ON post.ID = latitude.post_id
            INNER JOIN $wpdb->postmeta longitude ON post.ID = longitude.post_id
            AND post.post_type   = 'location'
            AND post.post_status = 'publish'
            AND latitude.meta_key = 'latitude'
            AND longitude.meta_key = 'longitude'
            HAVING distance <= %s
            ORDER BY distance ASC",
            $earth_radius,
            $lat,
            $lng,
            $lat,
            $distance
        );

        $nearby_locations = $wpdb->get_results($sql, ARRAY_A);

        if ($nearby_locations) {
            return $nearby_locations;
        }
    }
}
