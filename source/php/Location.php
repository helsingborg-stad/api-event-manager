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
        if(!isset($this->_event_manager_uid))
        {
            $wholeAddress = $this->post_title;
            $wholeAddress .= $this->city != null ? ', ' . $this->city : '';

            $res = Helper\Address::gmapsGetAddressComponents($wholeAddress);

            if (!isset($res->geometry->location) || !is_object($res)) {
                wp_delete_post($this->ID, true);
                return false;
            }

            update_post_meta($this->ID, 'map', array(
                'address' => $res->formatted_address,
                'lat' => $res->geometry->location->lat,
                'lng' => $res->geometry->location->lng
            ));

            $street = '';
            $streetNumber = '';
            foreach($res->address_components as $key => $component) {
                if($component->types[0] == 'postal_code')
                    update_post_meta($this->ID, 'postal_code', DataCleaner::number($component->long_name));
                if($component->types[0] == 'country')
                    update_post_meta($this->ID, 'country', $component->long_name);
                if($component->types[0] == 'route')
                    $street = $component->long_name;
                if($component->types[0] == 'street_number')
                    $streetNumber = $component->long_name;
            }

            if(!empty($street)) {
                if(!empty($streetNumber))
                    $street .= ' ' . $streetNumber;
                update_post_meta($this->ID, 'street_address', $street);
            }

            update_post_meta($this->ID, 'formatted_address', $res->formatted_address);
            update_post_meta($this->ID, 'latitude', $res->geometry->location->lat);
            update_post_meta($this->ID, 'longitude', $res->geometry->location->lng);
            update_post_meta($this->ID, '_event_manager_uid', $this->post_title);
        }
        else
        {
            $res = Helper\Address::gmapsGetAddressComponents($this->street_address . ' ' . $this->postal_code . ' ' . $this->city . ' ' . $this->country);

            if (!isset($res->geometry->location)) {
                return true;
            }

            update_post_meta($this->ID, 'map', array(
                'address' => $res->formatted_address,
                'lat' => $res->geometry->location->lat,
                'lng' => $res->geometry->location->lng
            ));

            update_post_meta($this->ID, 'formatted_address', $res->formatted_address);
        }
        return true;
    }
}
