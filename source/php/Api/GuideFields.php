<?php

namespace HbgEventImporter\Api;

/**
 * Adding meta fields to location post type
 */

class GuideFields extends Fields
{
    private $postType = 'guide';

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
            'theme',
            array(
                'get_callback' => array($this, 'theme'),
                'schema' => array(
                    'description' => 'Field containing object with taxonomies.',
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
                    'description' => 'Field containing object with taxonomies.',
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
                    'description' => 'Field containing object with taxonomies.',
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
                    'description' => 'Field containing object with taxonomies.',
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
                    'description' => 'Field containing object with taxonomies.',
                    'type' => 'object',
                    'context' => array('view')
                )
            )
        );
    }

    public function theme($object, $field_name, $request, $formatted = true)
    {
        $theme = array(
            'id' => $this->numericGetCallBack($object, 'guide_apperance_data', $request),
            'name' => $this->numericGetCallBack($object, 'guide_apperance_data', $request),
            'logotype' => $this->numericGetCallBack($object, 'guide_apperance_data', $request),
            'color' => $this->numericGetCallBack($object, 'guide_apperance_data', $request)
        );

        if (empty(array_filter($theme))) {
            return null;
        } else {
            return $theme;
        }
    }

    public function beacon($object, $field_name, $request, $formatted = true)
    {
        $beacon = array(
            'namespace' => $this->numericGetCallBack($object, 'guide_main_beacon_namespace', $request, $formatted),
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

        foreach ((array) $this->objectGetCallBack($object, 'guide_location_objects', $request, true) as $item) {
            $objects[] = array(
                'id' => empty($item['guide_object_id']) ? null : $item['guide_object_id'],
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

        return $objects;
    }

    public function addHalLink($object, $post_id)
    {
        $response = new \WP_REST_Response($object);
        $response->add_link(
            'blaha',
            rest_url('/wp/v2/users/42'),
            array( 'embeddable' => true )
        );
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
