<?php

namespace HbgEventImporter\Api;

/**
 * Adding meta fields to location post type
 */

class LocationMeta
{

    private $postType = 'location';

    public function __construct()
    {
        add_action('rest_api_init', array($this,'postalCode'));
    }

    public function postalCode() {
        register_rest_field($this->postType,
            'postal_code',
            array(
                'get_callback' => $this->standardGetCallBack(),
                'update_callback' => array($this,'numericUpdateCallBack'),
                'schema' => array(
                    'description' => 'Field contianing numeric value with postal code.',
                    'type' => 'number',
                    'context' => array('view', 'edit')
                )
            )
        );
    }

    /**
     * Common action functions
     */

    public function standardGetCallBack( $object, $field_name, $request ) {
        return "1465456465";
        return get_post_meta( $object[ 'id' ], $field_name );
    }

    public function stringUpdateCallBack( $value, $object, $field_name ) {
        if ( ! $value || ! is_string( $value ) ) {
            return;
        }
        return update_post_meta( $object->ID, $field_name, strip_tags( $value ) );
    }

    public function numericUpdateCallBack( $value, $object, $field_name ) {
        if ( ! $value || ! is_numeric( $value ) ) {
            return;
        }
        return update_post_meta( $object->ID, $field_name, $value );
    }
}
