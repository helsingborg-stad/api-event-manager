<?php

namespace EventManager\PostToSchema\PostToEventSchema\Commands\Helpers;

use EventManager\PostToSchema\PostToEventSchema\Commands\Helpers\CommandHelpersInterface;

class CommandHelpers implements CommandHelpersInterface
{
    public function mapOpenStreetMapDataToPlace(array $openStreetMapData): ?\Spatie\SchemaOrg\Place
    {
        $place = new \Spatie\SchemaOrg\Place();
        $place->address($openStreetMapData['address'] ?? null);
        $place->latitude($openStreetMapData['lat'] ?? null);
        $place->longitude($openStreetMapData['lng'] ?? null);

        return $place;
    }
}
