<?php

namespace HbgEventImporter\Api;

/**
 * Adding meta fields to location post type
 */

class GuideFields extends Fields
{
    private $postType = 'guide';
    private $taxonomyName = 'guidegroup';
    private $objectCache = array();

    public function __construct()
    {
        add_action('rest_api_init', array($this, 'registerRestFields'));

        add_action('rest_api_init', array($this, 'registerTaxonomyRestFields'));
    }

    /**
     * Register rest fields to consumer api
     * @return  void
     * @version 0.3.2 creating consumer accessable meta values.
     */

    public static function registerTaxonomyRestFields()
    {
        register_rest_field($this->taxonomyName,
            'apperance',
            array(
                'get_callback' => null,
                'schema' => array(
                    'description' => 'Describes the guides colors, logo, moodimage and main location.',
                    'type' => 'object',
                    'context' => array('view', 'embed')
                )
            )
        );
    }


    /**
     * Register rest fields to consumer api
     * @return  void
     * @version 0.3.2 creating consumer accessable meta values.
     */
    public static function registerRestFields()
    {
        register_rest_field($this->postType,
            'apperance',
            array(
                'get_callback' => array($this, 'apperance'),
                'schema' => array(
                    'description' => 'Describes the guides colors, logo, moodimage and main location.',
                    'type' => 'object',
                    'context' => array('view', 'embed')
                )
            )
        );

        // Main attraction data
        register_rest_field($this->postType,
            'mainAttraction',
            array(
                'get_callback' => array($this, 'settings'),
                'schema' => array(
                    'description' => 'Describes the guides colors, logo, moodimage and main location.',
                    'type' => 'object',
                    'context' => array('view', 'embed')
                )
            )
        );

        // Guide theme object
        /*register_rest_field($this->postType,
            'subAttractions',
            array(
                'get_callback' => array($this, 'settings'),
                'schema' => array(
                    'description' => 'Describes the guides colors, logo, moodimage and main location.',
                    'type' => 'object',
                    'context' => array('view', 'embed')
                )
            )
        );
*/



/*


        // Guide main location
        register_rest_field($this->postType,
            'location',
            array(
                'get_callback' => array($this, 'mainLocation'),
                'schema' => array(
                    'description' => 'The main location for this guide.',
                    'type' => 'object',
                    'context' => array('view', 'embed')
                )
            )
        );

        // Guide beacon object
        register_rest_field($this->postType,
            'beacon',
            array(
                'get_callback' => array($this, 'beacon'),
                'schema' => array(
                    'description' => 'Guide main beacon information.',
                    'type' => 'object',
                    'context' => array('view', 'embed')
                )
            )
        );

        // Guide media objects
        register_rest_field($this->postType,
            'media',
            array(
                'get_callback' => array($this, 'media'),
                'schema' => array(
                    'description' => 'Guide main media information.',
                    'type' => 'object',
                    'context' => array('view', 'embed')
                )
            )
        );

        // Guide location objects
        register_rest_field($this->postType,
            'contentObjects',
            array(
                'get_callback' => array($this, 'objects'),
                'schema' => array(
                    'description' => 'Objects of this guide.',
                    'type' => 'object',
                    'context' => array('view', 'embed')
                )
            )
        );

        // Guide location objects
        register_rest_field($this->postType,
            'walk',
            array(
                'get_callback' => array($this, 'walk'),
                'schema' => array(
                    'description' => 'Objects of this guide.',
                    'type' => 'object',
                    'context' => array('view', 'embed')
                )
            )
        );*/
    }

    public function walk($object, $field_name, $request, $formatted = true)
    {
        if ($this->boolGetCallBack($object, 'guide_enable_stops', $request, $formatted)) {
            return $this->objectGetCallBack($object, 'guide_object_stops', $request);
        }
        return null;
    }

    public function apperance($object, $field_name, $request, $formatted = true)
    {
        $taxonomy = $this->objectGetCallBack($object, 'guide_group', $request);

        return array(
            'logotype'  => $this->sanitizeMediaObject(get_field('guide_taxonomy_logotype', $taxonomy->taxonomy. '_' . $taxonomy->term_id)),
            'color'     => get_field('guide_taxonomy_color', $taxonomy->taxonomy. '_' . $taxonomy->term_id),
            'moodimage' => $this->sanitizeMediaObject(get_field('guide_taxonomy_image', $taxonomy->taxonomy. '_' . $taxonomy->term_id)),
        );
    }

    public function settings($object, $field_name, $request, $formatted = true)
    {
        $taxonomy       = $this->objectGetCallBack($object, 'guide_group', $request);
        $location_id    = (int) get_field('guide_taxonomy_location', $taxonomy->taxonomy. '_' . $taxonomy->term_id, false);

        return array(
            'id'            => is_numeric($taxonomy->term_id) && !empty($taxonomy->term_id) ? $taxonomy->term_id : null,
            'name'          => !empty($taxonomy->name) ? $taxonomy->name : null,
            'description'   => !empty($taxonomy->description) ? $taxonomy->description : null,
            'location'      => is_numeric($location_id) && !empty($location_id) ? $location_id : null,
        );
    }

    public function beacon($object, $field_name, $request, $formatted = true)
    {
        $beacon = array(
            'namespace' => $this->stringGetCallBack($object, 'guide_main_beacon_namespace', $request, $formatted),
            'distance' => $this->numericGetCallBack($object, 'guide_main_beacon_distance', $request, $formatted)
        );

        if (empty(array_filter($beacon))) {
            return null;
        } else {
            return $beacon;
        }
    }

    public function media($object, $field_name, $request, $formatted = true)
    {
        $media =    $this->sanitinzeMediaObjectArray(
                        $this->objectGetCallBack($object, 'guide_main_media', $request, true)
                    );

        if (empty(array_filter($media))) {
            return null;
        } else {
            return $media;
        }
    }

    public function objects($object, $field_name, $request, $formatted = true)
    {
        $objects = [];

        foreach ($this->getObjects($object, 'guide_location_objects', $request, true) as $key => $item) {
            $objects[] = array(
                'active' => ($item['guide_object_active'] == 1) ? true : false,
                'id' => empty($item['guide_object_id']) ? null : $item['guide_object_id'],

                'title' => empty($item['guide_object_title']) ? null : $item['guide_object_title'],
                'description' => empty($item['guide_object_description']) ? null : $item['guide_object_description'],

                'image' => !is_array($item['guide_object_image']) ? null : $this->sanitizeMediaObject($item['guide_object_image']),
                'audio' => !is_array($item['guide_object_audio']) ? null : $this->sanitizeMediaObject($item['guide_object_audio']),
                'video' => !is_array($item['guide_object_video']) ? null : $this->sanitizeMediaObject($item['guide_object_video']),
                'links' => !is_array($item['guide_object_links']) ? null : $this->sanitizeLinkObject($item['guide_object_links']),

                //'sublocation' => !is_numeric($item['guide_object_location']) ? null : $item['guide_object_location'],

                'beacon' => array(
                    'id' => empty($item['guide_object_beacon_id']) ? null : $item['guide_object_beacon_id'],
                    'distance' => !is_numeric($item['guide_object_beacon_distance']) ? null : $item['guide_object_beacon_distance']
                ),
            );
        }

        return (array) $objects;
    }

    public function mainLocation($object, $field_name, $request, $formatted = true)
    {
        return $this->numericGetCallBack($object, 'guide_main_location', $request, true);
    }

    public function getObjects($object, $field_name, $request, $formatted = true)
    {
        $hash = md5(json_encode($object));

        if (isset($this->objectCache[$hash]) && !empty($this->objectCache[$hash])) {
            return $this->objectCache[$hash];
        }

        return $this->objectCache[$hash] = $this->objectGetCallBack($object, 'guide_location_objects', $request, true);
    }

    public function sanitizeMediaObject($item)
    {
        if (is_array($item)) {
            unset($item['ID']);
            unset($item['filename']);
            unset($item['author']);
            unset($item['caption']);
            unset($item['icon']);
        }

        return $item;
    }

    public function sanitinzeMediaObjectArray($objectArray)
    {
        if (array_values($objectArray) !== $objectArray) {
            return $objectArray;
        }

        foreach ((array) $objectArray as $key => $item) {
            $objectArray[$key] = $this->sanitizeMediaObject($item);
        }

        return $objectArray;
    }

    public function sanitizeLinkObject($linkObject)
    {
        $sanitizedLinkObject = [];

        foreach ((array) $linkObject as $key => $item) {
            if (!filter_var($item['guide_object_link_url'], FILTER_VALIDATE_URL) === false) {
                $sanitizedLinkObject[$key] = array(
                    'title' => $item['guide_object_link_title'],
                    'link' => $item['guide_object_link_url']
                );
            }
        }

        return $sanitizedLinkObject;
    }
}
