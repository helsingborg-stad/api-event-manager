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
                'get_callback' => array($this, 'taxonomyApperance'),
                'schema' => array(
                    'description' => 'Describes the guides colors, logo and mood image.',
                    'type' => 'object',
                    'context' => array('view', 'embed')
                )
            )
        );

        register_rest_field($this->taxonomyName,
            'settings',
            array(
                'get_callback' => array($this, 'taxonomySettings'),
                'schema' => array(
                    'description' => 'Describes  main location in generel terms.',
                    'type' => 'object',
                    'context' => array('view', 'embed')
                )
            )
        );

        register_rest_field($this->taxonomyName,
            'notice',
            array(
                'get_callback' => array($this, 'taxonomyNotice'),
                'schema' => array(
                    'description' => 'Describes  main location in generel terms.',
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
            'guideBeacon',
            array(
                'get_callback' => array($this, 'postBeacon'),
                'schema' => array(
                    'description' => 'Objects of this guide.',
                    'type' => 'object',
                    'context' => array('view', 'embed')
                )
            )
        );

        // Guide media objects
        register_rest_field($this->postType,
            'guideMedia',
            array(
                'get_callback' => array($this, 'postMedia'),
                'schema' => array(
                    'description' => 'Guide main media information.',
                    'type' => 'object',
                    'context' => array('view', 'embed')
                )
            )
        );

        register_rest_field($this->postType,
            'contentObjects',
            array(
                'get_callback' => array($this, 'postObjects'),
                'schema' => array(
                    'description' => 'Objects of this guide.',
                    'type' => 'object',
                    'context' => array('view', 'embed')
                )
            )
        );

        register_rest_field($this->postType,
            'subAttractionBeacon',
            array(
                'get_callback' => array($this, 'subAttractionBeacons'),
                'schema' => array(
                    'description' => 'Describes the guides colors, logo, moodimage and main location.',
                    'type' => 'object',
                    'context' => array('view', 'embed')
                )
            )
        );
    }

    /* TAXONOMY */

    private function taxonomyKey($taxonomy)
    {
        return $taxonomy['taxonomy']. '_' . $taxonomy['id'];
    }

    private function convertToNull($value, $convertValueToNull = false)
    {
        if ($value == $convertValueToNull) {
            return null;
        }
        return $value;
    }

    public function taxonomyApperance($object, $field_name, $request, $formatted = true)
    {
        return array(
            'logotype'          => get_field('guide_taxonomy_logotype', $this->taxonomyKey($object)),
            'color'             => get_field('guide_taxonomy_color', $this->taxonomyKey($object)),
            'image'             => get_field('guide_taxonomy_image', $this->taxonomyKey($object))
        );
    }

    public function taxonomySettings($object, $field_name, $request, $formatted = true)
    {
        return array(
            'active'            => get_field('guide_taxonomy_active', $this->taxonomyKey($object)),
            'location'          => get_field('guide_taxonomy_location', $this->taxonomyKey($object)),
            'locationFilter'    => get_field('guide_taxonomy_sublocations', $this->taxonomyKey($object)),
            'wifi'              => get_field('guide_taxonomy_wifi', $this->taxonomyKey($object)),
            'map'               => get_field('guide_taxonomy_map', $this->taxonomyKey($object))
        );
    }

    public function taxonomyNotice($object, $field_name, $request, $formatted = true)
    {
        return array(
            'arrival'           => $this->convertToNull(get_field('guide_arrival_notice', $this->taxonomyKey($object))),
            'departure'         => $this->convertToNull(get_field('guide_arrival_notice', $this->taxonomyKey($object))),
        );
    }

    /* Posttype */

    public function subAttractionBeacons($object, $field_name, $request, $formatted = true)
    {
        $result             = array();
        $beacons            = $this->objectGetCallBack($object, 'guide_beacon', $request, true);
        $objects            = $this->getObjects($object, 'guide_content_objects', $request, true);
        $beacon_namespace   = $this->stringGetCallBack($object, 'guide_beacon_namespace', $request, $formatted);

        foreach ($beacons as $key => $item) {
            if (!empty($item['objects'])) {
                $result[] = array(
                    'nid' => $beacon_namespace,
                    'bid' => $item['beacon'],
                    'content' => $item['objects'],
                    'location' => is_numeric($item['location']) ? $item['location'] : null
                );
            }
        }

        if (!array_filter($result)) {
            return null;
        }

        return $result;
    }

    public function postBeacon($object, $field_name, $request, $formatted = true)
    {
        $beacon = array(
            'nid' => $this->stringGetCallBack($object, 'guide_beacon_namespace', $request, $formatted)
        );

        if (empty(array_filter($beacon))) {
            return null;
        } else {
            return $beacon;
        }
    }

    public function postObjects($object, $field_name, $request, $formatted = true)
    {
        $objects = [];

        foreach ($this->getObjects($object, 'guide_content_objects', $request, true) as $key => $item) {
            $objects[$key] = array(
                'active' => ($item['guide_object_active'] == 1) ? true : false,
                'id' => empty($item['guide_object_id']) ? null : $item['guide_object_id'],

                'title' => empty($item['guide_object_title']) ? null : $item['guide_object_title'],
                'description' => empty($item['guide_object_description']) ? null : $item['guide_object_description'],

                'image' => !is_array($item['guide_object_image']) ? null : $this->sanitizeMediaObject($item['guide_object_image']),
                'audio' => !is_array($item['guide_object_audio']) ? null : $this->sanitizeMediaObject($item['guide_object_audio']),
                'video' => !is_array($item['guide_object_video']) ? null : $this->sanitizeMediaObject($item['guide_object_video']),
                'links' => !is_array($item['guide_object_links']) ? null : $this->sanitizeLinkObject($item['guide_object_links']),
            );
        }

        return (array) $objects;
    }

    public function postMedia($object, $field_name, $request, $formatted = true)
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

    public function getObjects($object, $field_name, $request, $formatted = true)
    {

        //Create cache hash
        $hash = base_convert(md5(json_encode($object)), 10, 36);

        //Look for cache (not persistent)
        if (isset($this->objectCache[$hash]) && !empty($this->objectCache[$hash])) {
            return $this->objectCache[$hash];
        }

        //Nope, get new content
        $objects = [];
        foreach ((array) $this->objectGetCallBack($object, 'guide_content_objects', $request, true) as $key => $item) {
            $objects[$item['guide_object_uid']] = $item;
        }

        //Return and save to cache (not persistent)
        return $this->objectCache[$hash] = $objects;
    }

    /* Sanitize functions */

    public function sanitizeMediaObject($item)
    {
        if (is_array($item)) {
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
