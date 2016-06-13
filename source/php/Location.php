<?php

namespace HbgEventImporter;

class Location extends \HbgEventImporter\Entity\PostManager
{
    public $postType = 'location';

    public function afterSave()
    {
        $coordinates = Helper\Address::getCoordinatesByAddress($this->postalAddress . ' ' . $this->postcode . ' ' . $this->city);

        update_post_meta($this->ID, 'map', array(
            'address' => $this->postalAddress . ', ' . $this->postcode . ' ' . $this->city . ', ' . $this->country,
            'lat' => $coordinates->lat,
            'lng' => $corrdinates->lng
        ));
    }
}
