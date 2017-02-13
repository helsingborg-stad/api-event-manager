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
                'get_callback' => array($this, 'stringGetCallBack'),
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
        return array(
            'id' => $this->numericGetCallBack($object, 'guide_apperance_data', $request),
            'logotype' => $this->numericGetCallBack($object, 'guide_apperance_data', $request),
            'color' => $this->numericGetCallBack($object, 'guide_apperance_data', $request)
        );
    }

    public function beacon($object, $field_name, $request, $formatted = true)
    {
        return array(
            'enabled' => $this->unformattedNumericGetCallBack($object, 'guide_main_use_beacon', $request, false),
            'namespace' => $this->numericGetCallBack($object, 'guide_main_beacon_namespace', $request, $formatted),
            'distance' => $this->numericGetCallBack($object, 'guide_main_beacon_distance', $request, $formatted)
        );
    }

    public function objects($object, $field_name, $request, $formatted = true)
    {
        $objects = [];

        foreach ((array) $this->objectGetCallBack($object, 'guide_location_objects', $request, true) as $item) {
            $objects[] = array(
                'id' => empty($item['guide_object_id']) ? null : $item['guide_object_id'],
                'title' => empty($item['guide_object_title']) ? null : $item['guide_object_title'],
                'description' => empty($item['guide_object_description']) ? null : $item['guide_object_description'],

                'image' => !is_numeric($item['guide_object_image']) ? null : $item['guide_object_image'],
                'audio' => !is_numeric($item['guide_object_audio']) ? null : $item['guide_object_audio'],
                'video' => !is_numeric($item['guide_object_video']) ? null : $item['guide_object_video'],
                'links' => !is_array($item['guide_object_links']) ? null : $item['guide_object_links'],

                'sublocation' => !is_numeric($item['guide_object_location']) ? null : $item['guide_object_location'],

                'beacon' => array(
                    'id' => !is_numeric($item['guide_object_beacon_id']) ? null : $item['guide_object_beacon_id'],
                    'distance' => !is_numeric($item['guide_object_beacon_distance']) ? null : $item['guide_object_beacon_distance']
                ),
            );

            //Add HAL links to locations
            if (!is_null($item['guide_object_location']) && is_numeric($item['guide_object_location'])) {
                $this->addHalLink($item['guide_object_location']);
            }
        }

        return $objects;
    }

    public function addHalLink($post_id)
    {
    }
}
