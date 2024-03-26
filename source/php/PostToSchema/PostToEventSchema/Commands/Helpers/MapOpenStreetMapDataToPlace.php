<?php

namespace EventManager\PostToSchema\PostToEventSchema\Commands\Helpers;

interface MapOpenStreetMapDataToPlace
{
    public function mapOpenStreetMapDataToPlace(array $openStreetMapData): ?\Spatie\SchemaOrg\Place;
}
