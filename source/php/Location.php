<?php

namespace HbgEventImporter;

class Location extends \HbgEventImporter\Entity\PostManager
{
    public $post_type = 'location';

    public function afterSave()
    {
        $res = Helper\Address::gmapsGetAddressComponents($this->postal_address . ' ' . $this->postal_code . ' ' . $this->city . ' ' . $this->country);

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
