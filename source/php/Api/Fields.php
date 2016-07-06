<?php

namespace HbgEventImporter\Api;

/**
 * Default get and save functions
 */

class Fields
{
    public function numericGetCallBack($object, $field_name, $request)
    {
        $return_value = self::getFieldGetMetaData($object, $field_name, $request);

        if (is_numeric($return_value)) {
            return $return_value;
        } else {
            return null;
        }
    }

    public function stringGetCallBack($object, $field_name, $request)
    {
        $return_value = self::getFieldGetMetaData($object, $field_name, $request);

        if (is_string($return_value) && !empty($return_value)) {
            return $return_value;
        } else {
            return null;
        }
    }

    public function objectGetCallBack($object, $field_name, $request)
    {
        $return_value = self::getFieldGetMetaData($object, $field_name, $request);

        if (is_array($return_value)||is_object($return_value) && !empty($return_value)) {
            return $return_value;
        } else {
            return null;
        }
    }

    public function stringUpdateCallBack($value, $object, $field_name)
    {
        if (! $value || ! is_string($value)) {
            return;
        }
        return update_post_meta($object->ID, $field_name, strip_tags($value));
    }

    public function numericUpdateCallBack($value, $object, $field_name)
    {
        if (! $value || ! is_numeric($value)) {
            return;
        }
        return update_post_meta($object->ID, $field_name, $value);
    }

    public function objectUpdateCallBack($value, $object, $field_name)
    {
        if (! $value || ! is_object($value) && ! is_array($value)) {
            return;
        }
        return update_post_meta($object->ID, $field_name, $value);
    }

    public static function getFieldGetMetaData($object, $field_name, $request)
    {
        if (function_exists('get_field')) {
            return get_field($field_name, $object['id']);
        } else {
            return get_post_meta($object['id'], $field_name, true);
        }
    }
}
