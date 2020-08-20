<?php

namespace HbgEventImporter;

class Translation
{
    public function __construct()
    {
        add_filter('wp-content-translator/configuration/meta', array($this, 'translationConfigurationGuides'));
    }

    public function translationConfigurationGuides($config)
    {
        array_push(
            $config->untranslatable,
            "guidegroup",
            "guide_kids",
            "guide_location"
          );

        //Repeating fields
        for ($x = 0; $x <= 100; $x++) {
            array_push(
                $config->translatable,
                "guide_beacon_".$x."_location",
                "guide_beacon_".$x."_distance",
                "guide_beacon_".$x."_objects",
                "guide_content_objects_".$x."_guide_object_title",
                "guide_content_objects_".$x."_guide_object_id",
                "guide_content_objects_".$x."_guide_object_description",
                "guide_content_objects_".$x."_guide_object_image",
                "guide_content_objects_".$x."_guide_object_audio",
                "guide_content_objects_".$x."_guide_object_video",
                "guide_content_objects_".$x."_guide_object_links",
                "guide_content_objects_".$x."_guide_object_uid",
                "guide_content_objects_".$x."_guide_object_active"
            );
        }

        return $config;
    }
}
