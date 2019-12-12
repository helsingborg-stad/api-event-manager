<?php

namespace HbgEventImporter\Api;

/**
 * Adding meta fields to location post type
 */

class PointPropertyFields extends Fields
{
    private $taxonomy = 'property';

    public function __construct()
    {
        add_action('rest_api_init', array($this, 'registerRestFields'));
    }

    /**
     * Register rest fields to consumer api
     * @return  void
     */
    public function registerRestFields()
    {
        // Title as plain text
        register_rest_field($this->taxonomy,
            'icon',
            array(
                'get_callback'    => array($this, 'getTaxonomyImage'),
                'update_callback' => null,
                'schema'          => null,
            )
        );
    }

    public function getTaxonomyImage($object, $field_name, $request)
    {
        $image = get_field('point_property_image', $this->taxonomy . '_' . $object['id']);

        if ($image) {
            return $image;
        }

        return null;
    }
}
