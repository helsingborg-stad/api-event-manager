<?php

namespace HbgEventImporter\Api;

/**
 * Adding meta fields to location post type
 */

class GuideFields extends Fields
{
    private $postType = 'guide';
    private $objectCache = array();

    public function __construct()
    {
        add_action('rest_api_init', array($this, 'registerRestFields'));
    }

    /**
     * Register rest fields to consumer api
     * @return  void
     * @version 0.3.2 creating consumer accessable meta values.
     */
    public static function registerRestFields()
    {

        // Guide theme object
        register_rest_field($this->postType,
            'settings',
            array(
                'get_callback' => array($this, 'settings'),
                'schema' => array(
                    'description' => 'Describes the guides colors and logo.',
                    'type' => 'object',
                    'context' => array('view')
                )
            )
        );

        // Guide main location
        register_rest_field($this->postType,
            'location',
            array(
                'get_callback' => array($this, 'mainLocation'),
                'schema' => array(
                    'description' => 'The main location for this guide.',
                    'type' => 'object',
                    'context' => array('view')
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
                    'context' => array('view')
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
                    'context' => array('view')
                )
            )
        );

        // Guide location objects
        register_rest_field($this->postType,
            'objects',
            array(
                'get_callback' => array($this, 'objects'),
                'schema' => array(
                    'description' => 'Objects of this guide.',
                    'type' => 'object',
                    'context' => array('view')
                )
            )
        );

        // Guide location objects
        register_rest_field($this->postType,
            'objectMap',
            array(
                'get_callback' => array($this, 'objectMap'),
                'schema' => array(
                    'description' => 'Map item for mapping objects to locations and beacon. Each value reperesents the object array key. Hidden objects will not be mirrored in this strucure object.',
                    'type' => 'object',
                    'context' => array('view')
                )
            )
        );
    }

    public function settings($object, $field_name, $request, $formatted = true)
    {
        $settings = [];

        /* Theme */
        $taxonomy = $this->objectGetCallBack($object, 'guide_apperance_data', $request);
        $settings['theme'] = array(
            'id' => $taxonomy->term_id,
            'name' => $taxonomy->name,
            'logotype' => $this->sanitizeMediaObject(get_field('guide_taxonomy_logotype', $taxonomy->taxonomy. '_' . $taxonomy->term_id)),
            'color' => get_field('guide_taxonomy_color', $taxonomy->taxonomy. '_' . $taxonomy->term_id),
            'moodimage' => $this->sanitizeMediaObject(get_field('guide_taxonomy_image', $taxonomy->taxonomy. '_' . $taxonomy->term_id)),
            'taxonomy' => $taxonomy
        );

        /* Wheter to use map or not */
        $settings['map'] = $this->boolGetCallBack($object, 'guide_main_map', $request, $formatted);

        /* Wheter the location has full wifi coverage or not */
        $settings['wifi'] = $this->boolGetCallBack($object, 'guide_main_wifi', $request, $formatted);

        /* Check if guide has objects - If not its disabled */
        $settings['objects'] = empty($this->getObjects($object, 'guide_location_objects', $request, true)) ? false : true;

        /* Arrival messages */
        $settings['messages'] = array(
            'arrival' => array(
                array(
                    'message' => 'Välkommen till oss, du vet väl att vi bjuder på bullar?',
                    'delay' => 5,
                )
            ),
            'departue' => array(
                array(
                    'message' => 'Tack för ditt besök, välkommen åter!',
                    'delay' => 5,
                ),
                array(
                    'message' => 'Hur upplevde du ditt besök igår? Ge oss ett betyg på http://dunkerskulturs.se/betyg/',
                    'delay' => 86400,
                )
            )
        );

        return $settings;
    }

    public function objectMap($object, $field_name, $request, $formatted = true)
    {

        //Get objects
        $objects = $this->getObjects($object, 'guide_location_objects', $request, true);

        //Result structure definition
        $structured = array(
            'beacon' => array(),
            'location' => array(
                'undefined' => array(),
                'sublocation' => array()
            )
        );

        //Create location map
        foreach ((array) $objects as $key => $item) {
            if ($item['guide_object_active'] != 1) {
                continue;
            }

            if (empty($item['guide_object_location'])) {
                $structured['location']['undefined'][] = $key;
            } else {
                if (!is_array($structured['location']['sublocation'])) {
                    $structured['location']['sublocation'][$item['guide_object_location']] = array($key);
                }
                $structured['location']['sublocation'][$item['guide_object_location']][] = $key;
            }
        }

        //Create beacon map
        foreach ((array) $objects as $key => $item) {
            if ($item['guide_object_active'] != 1) {
                continue;
            }

            if (!empty($item['guide_object_beacon_id'])) {
                if (!is_array($structured['beacon'])) {
                    $structured['beacon'][$item['guide_object_beacon_id']] = array($key);
                }
                $structured['beacon'][$item['guide_object_beacon_id']][] = $key;
            }
        }

        return $structured;
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

    public function getObjects($object, $field_name, $request, $formatted = true)
    {
        $hash = md5(json_encode($object));

        if (isset($this->objectCache[$hash]) && !empty($this->objectCache[$hash])) {
            return $this->objectCache[$hash];
        }

        return $this->objectCache[$hash] = $this->objectGetCallBack($object, 'guide_location_objects', $request, true);
    }

    public function objects($object, $field_name, $request, $formatted = true)
    {
        $objects = [];

        foreach ($this->getObjects($object, 'guide_location_objects', $request, true) as $key => $item) {
            $objects[] = array(
                'active' => ($item['guide_object_active'] == 1) ? true : false,
                'id' => empty($item['guide_object_id']) ? null : $item['guide_object_id'],
                'order' => $key,

                'title' => empty($item['guide_object_title']) ? null : $item['guide_object_title'],
                'description' => empty($item['guide_object_description']) ? null : $item['guide_object_description'],

                'image' => !is_array($item['guide_object_image']) ? null : $this->sanitizeMediaObject($item['guide_object_image']),
                'audio' => !is_array($item['guide_object_audio']) ? null : $this->sanitizeMediaObject($item['guide_object_audio']),
                'video' => !is_array($item['guide_object_video']) ? null : $this->sanitizeMediaObject($item['guide_object_video']),
                'links' => !is_array($item['guide_object_links']) ? null : $this->sanitizeLinkObject($item['guide_object_links']),

                'sublocation' => !is_numeric($item['guide_object_location']) ? null : $item['guide_object_location'],

                'beacon' => array(
                    'id' => !is_numeric($item['guide_object_beacon_id']) ? null : $item['guide_object_beacon_id'],
                    'distance' => !is_numeric($item['guide_object_beacon_distance']) ? null : $item['guide_object_beacon_distance']
                ),
            );
        }

        return (array) $objects;
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

    public function mainLocation($object, $field_name, $request, $formatted = true)
    {
        return $this->numericGetCallBack($object, 'guide_main_location', $request, true);
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
