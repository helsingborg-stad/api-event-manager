<?php

namespace HbgEventImporter;

use \HbgEventImporter\Helper\DataCleaner as DataCleaner;

class Location extends \HbgEventImporter\Entity\PostManager
{
    public $post_type = 'location';

    /**
     * Stuff to do before save
     * @return void
     */
    public function beforeSave()
    {
        $this->post_title = !is_string($this->post_title) ? $this->post_title : DataCleaner::string($this->post_title);
        $this->street_address = !is_string($this->street_address) ? $this->street_address : DataCleaner::string($this->street_address);
        $this->postal_code = DataCleaner::number($this->postal_code);
        $this->city = !is_string($this->city) ? $this->city : DataCleaner::string($this->city);
        $this->municipality = !is_string($this->municipality) ? $this->municipality : DataCleaner::string($this->municipality);
        $this->country = !is_string($this->country) ? $this->country : DataCleaner::string($this->country);
        $this->latitude = !is_string($this->latitude) ? $this->latitude : DataCleaner::string($this->latitude);
        $this->longitude = !is_string($this->longitude) ? $this->longitude : DataCleaner::string($this->longitude);
        $this->_event_manager_uid = !is_string($this->_event_manager_uid) ? $this->_event_manager_uid : DataCleaner::string($this->_event_manager_uid);
    }

    /**
     * Stuff to do after save
     * @return void
     */
    public function afterSave()
    {
        $this->saveGroups();

        // Get address and coordinates from post title.
        if(!isset($this->_event_manager_uid) || ($this->street_address == null && ($this->latitude == null || $this->longitude == null)))
        {
            $type = $this->street_address != null ? true : false;
            $wholeAddress = $this->street_address != null ? $this->street_address : $this->post_title;
            $wholeAddress .= $this->city != null ? ', ' . $this->city : '';
            $res = Helper\Address::gmapsGetAddressComponents($wholeAddress, $type);
            if(!$res) {
                wp_delete_post($this->ID, true);
                return false;
            }
            update_post_meta($this->ID, 'street_address', $res->street);
            update_post_meta($this->ID, 'postal_code', $res->postalcode);
            update_post_meta($this->ID, 'city', $res->city);
            update_post_meta($this->ID, 'country', $res->country);
            update_post_meta($this->ID, 'formatted_address', $res->formatted_address);
            update_post_meta($this->ID, 'latitude', $res->latitude);
            update_post_meta($this->ID, 'longitude', $res->longitude);
            update_post_meta($this->ID, '_event_manager_uid', $this->post_title);

        // Get coordinates from address.
        } elseif ($this->street_address != null && ($this->latitude == null || $this->longitude == null)) {
            $res = Helper\Address::gmapsGetAddressComponents($this->street_address . ' ' . $this->postal_code . ' ' . $this->city . ' ' . $this->country, true);
            if (!isset($res->latitude)) {
                return true;
            }
            update_post_meta($this->ID, 'formatted_address', $res->formatted_address);
            update_post_meta($this->ID, 'latitude', $res->latitude);
            update_post_meta($this->ID, 'longitude', $res->longitude);

        // Get address from coordinates.
        } elseif ($this->street_address == null && $this->latitude != null && $this->longitude != null) {
            $res = Helper\Address::gmapsGetAddressByCoordinates($this->latitude, $this->longitude);
            if (!isset($res->street)) {
                return true;
            }
            update_post_meta($this->ID, 'street_address', $res->street);
            update_post_meta($this->ID, 'postal_code', $res->postalcode);
            update_post_meta($this->ID, 'city', $res->city);
            update_post_meta($this->ID, 'country', $res->country);
            update_post_meta($this->ID, 'formatted_address', $res->formatted_address);

        // If address and coordinates exist, save formatted address.
        } else {
            $wholeAddress = $this->street_address;
            $wholeAddress .= $this->postal_code != null ? ', ' . $this->postal_code : '';
            $wholeAddress .= $this->city != null ? ', ' . $this->city : '';
            $wholeAddress .= $this->country != null ? ', ' . $this->country : '';
            update_post_meta($this->ID, 'formatted_address', $wholeAddress);
        }
        return true;
    }

    /**
     * Saves publishing groups as user_groups taxonomy terms
     * @return void
     */
    public function saveGroups()
    {
        wp_set_object_terms($this->ID, $this->user_groups, 'user_groups', true);
    }
}
