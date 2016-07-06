<?php

namespace HbgEventImporter;

use \HbgEventImporter\Helper\DataCleaner as DataCleaner;

class Location extends \HbgEventImporter\Entity\PostManager
{
    public $post_type = 'location';

    public function beforeSave()
    {
        $this->post_title = !is_string($this->post_title) ? $this->post_title : DataCleaner::string($this->post_title);
        $this->street_address = !is_string($this->street_address) ? $this->street_address : DataCleaner::string($this->street_address);
        $this->postal_code = !is_string($this->postal_code) ? $this->postal_code : DataCleaner::string($this->postal_code);
        $this->city = !is_string($this->city) ? $this->city : DataCleaner::string($this->city);
        $this->municipality = !is_string($this->municipality) ? $this->municipality : DataCleaner::string($this->municipality);
        $this->country = !is_string($this->country) ? $this->country : DataCleaner::string($this->country);
        $this->latitude = !is_string($this->latitude) ? $this->latitude : DataCleaner::string($this->latitude);
        $this->longitude = !is_string($this->longitude) ? $this->longitude : DataCleaner::string($this->longitude);
        $this->_event_manager_uid = !is_string($this->_event_manager_uid) ? $this->_event_manager_uid : DataCleaner::string($this->_event_manager_uid);
    }

    public function afterSave()
    {
        if(!isset($this->_event_manager_uid))
        {
            $res = Helper\Address::gmapsGetAddressComponents($this->post_title . ' ' . $this->city != null ? $this->city : '');

            if (!isset($res->geometry->location)) {
                return;
            }

            echo "Res: \n";
            var_dump($res);

            if (!isset($res->geometry->location)) {
                wp_delete_post($this->ID, true);
                return;
            }

            update_post_meta($this->ID, 'map', array(
                'address' => $res->formatted_address,
                'lat' => $res->geometry->location->lat,
                'lng' => $res->geometry->location->lng
            ));

            echo "ID: " . $this->ID . "\n";
            foreach($res->address_components as $key => $component) {
                if($component->types[0] == 'country')
                {
                    echo "Country found: " . $component->long_name . "\n";
                    update_post_meta($this->ID, 'country', $component->long_name);
                }

                /*
                update_post_meta($this->ID, 'street_address', $res->address_components[1]->long_name);
                update_post_meta($this->ID, 'postal_code', $res->address_components[5]->long_name);
                */
            }

            echo "End of component stuff!\n";

            update_post_meta($this->ID, 'formatted_address', $res->formatted_address);
            update_post_meta($this->ID, 'latitude', $res->geometry->location->lat);
            update_post_meta($this->ID, 'longitude', $res->geometry->location->lng);
            update_post_meta($this->ID, '_event_manager_uid', $this->post_title);
        }
        else
        {
            $res = Helper\Address::gmapsGetAddressComponents($this->street_address . ' ' . $this->postal_code . ' ' . $this->city . ' ' . $this->country);

            if (!isset($res->geometry->location)) {
                return;
            }

            update_post_meta($this->ID, 'map', array(
                'address' => $res->formatted_address,
                'lat' => $res->geometry->location->lat,
                'lng' => $res->geometry->location->lng
            ));

            update_post_meta($this->ID, 'formatted_address', $res->formatted_address);
        }
    }
}
