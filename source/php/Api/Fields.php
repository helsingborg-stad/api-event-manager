<?php

namespace HbgEventImporter\Api;

/**
 * Default get and save functions
 */

class Fields
{
    /**
     * Returning a numeric value formatted by acf if exist
     * @return  int, float, null
     * @version 0.3.0 creating consumer accessable meta values.
     */
    public function numericGetCallBack($object, $field_name, $request, $formatted = true)
    {
        $return_value = self::getFieldGetMetaData($object, $field_name, $request);

        if (is_numeric($return_value)) {
            return $return_value;
        } else {
            return null;
        }
    }

    /**
     * Returning a numeric value
     * @return  int, float, null
     * @version 0.3.0 creating consumer accessable meta values.
     */
    public function unformattedNumericGetCallBack($object, $field_name, $request)
    {
        return $this->numericGetCallBack($object, $field_name, $request, false);
    }

    /**
     * Returning a string value formatted by acf if exist
     * @return  string, null
     * @version 0.3.0 creating consumer accessable meta values.
     */
    public function stringGetCallBack($object, $field_name, $request, $formatted = true)
    {
        $return_value = self::getFieldGetMetaData($object, $field_name, $request);

        if (is_string($return_value) && !empty($return_value)) {
            return $return_value;
        } else {
            return null;
        }
    }

    /**
     * Returning a string value
     * @return  string, null
     * @version 0.3.0 creating consumer accessable meta values.
     */
    public function unformattedStringGetCallBack($object, $field_name, $request)
    {
        return $this->stringGetCallBack($object, $field_name, $request, false);
    }

    /**
     * Returning a object formatted by acf if exist
     * @return  object, null
     * @version 0.3.0 creating consumer accessable meta values.
     */
    public function objectGetCallBack($object, $field_name, $request, $formatted = true)
    {
        $return_value = self::getFieldGetMetaData($object, $field_name, $request);

        if (is_array($return_value)||is_object($return_value) && !empty($return_value)) {
            return $return_value;
        } else {
            return null;
        }
    }

    /**
     * Returning a object
     * @return  object, null
     * @version 0.3.0 creating consumer accessable meta values.
     */
    public function unformattedObjectGetCallBack($object, $field_name, $request)
    {
        return $this->objectGetCallBack($object, $field_name, $request, false);
    }

    /**
     * Update a string in database
     * @return  bool
     * @version 0.3.0 creating consumer accessable meta values.
     */
    public function stringUpdateCallBack($value, $object, $field_name)
    {
        if (! $value || ! is_string($value)) {
            return;
        }
        return update_post_meta($object->ID, $field_name, strip_tags($value));
    }

    /**
     * Update a int in database
     * @return  bool
     * @version 0.3.0 creating consumer accessable meta values.
     */
    public function numericUpdateCallBack($value, $object, $field_name)
    {
        if (! $value || ! is_numeric($value)) {
            return;
        }
        return update_post_meta($object->ID, $field_name, $value);
    }

    /**
     * Update a json-object in database
     * @return  bool
     * @version 0.3.0 creating consumer accessable meta values.
     */
    public function objectUpdateCallBack($value, $object, $field_name)
    {
        if (! $value || ! is_object($value) && ! is_array($value)) {
            return;
        }
        return update_post_meta($object->ID, $field_name, $value);
    }

    /**
     * Returning a formatted or unformatted meta field from database.
     * @return  int, string, object, null, bool
     * @version 0.3.0 creating consumer accessable meta values.
     */
    public static function getFieldGetMetaData($object, $field_name, $request, $formatted = true)
    {
        if (function_exists('get_field') && $formatted) {
            return get_field($field_name, $object['id']);
        } else {
            return get_post_meta($object['id'], $field_name, true);
        }
    }
}
