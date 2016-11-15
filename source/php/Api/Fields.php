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

    /**
     * Get complete list of event occasions
     *
     * @param array $object Details of current post.
     * @param string $field_name Name of field.
     * @param WP_REST_Request $request Current request
     *
     * @return array
     */
    public function getCompleteOccasions($object, $field_name, $request)
    {
        global $wpdb;
        $db_occasions = $wpdb->prefix . "occasions";
        $id = $object['id'];
        $data = array();
        $query_results = $wpdb->get_results("SELECT * FROM $db_occasions WHERE event = $id", OBJECT);
        // Get and save occasions from post meta
        $return_value = self::getFieldGetMetaData($object, 'occasions', $request);
        if (is_array($return_value)||is_object($return_value) && !empty($return_value)) {
            foreach ($return_value as $key => $value) {
                $data[] = array(
                'start_date'               => ($value['start_date']) ? $value['start_date'] : null,
                'end_date'                 => ($value['end_date']) ? $value['end_date'] : null,
                'door_time'                => ($value['door_time']) ? $value['door_time'] : null,
                'status'                   => ($value['status']) ? $value['status'] : null,
                'occ_exeption_information' => ($value['occ_exeption_information']) ? $value['occ_exeption_information'] : null,
                'content_mode'             => ($value['content_mode']) ? $value['content_mode'] : null,
                'content'                  => ($value['content']) ? $value['content'] : null,
                );
            }
        }
        // Save remaining occasions from occasions table to array
        foreach ($query_results as $key => $value) {
            $data[] = array(
                'start_date'               => ($value->timestamp_start) ? date('Y-m-d H:i', $value->timestamp_start) : null,
                'end_date'                 => ($value->timestamp_end) ? date('Y-m-d H:i', $value->timestamp_end) : null,
                'door_time'                => ($value->timestamp_door) ? date('Y-m-d H:i', $value->timestamp_door) : null,
                'status'                   => null,
                'occ_exeption_information' => null,
                'content_mode'             => null,
                'content'                  => null,
                );
        }
        $temp = array();
        $keys = array();
        // Remove duplicates from multi-array
        foreach ($data as $key => $val) {
            unset($val['status'], $val['occ_exeption_information'], $val['content_mode'], $val['content']);
            if (!in_array($val, $temp)) {
                $temp[] = $val;
                $keys[$key] = true;
            }
        }
        $return_data = array_intersect_key($data, $keys);
        // Sort array by start date
        usort($return_data, function ($x, $y) {
            return strcasecmp($x['start_date'], $y['start_date']);
        });

        if (empty($return_data)) {
            return null;
        }
        return $return_data;
    }

    /**
     * Adds more data to featured_media field.
     *
     * @param   object  $object      The response object.
     * @param   string  $field_name  The name of the field to add.
     * @param   object  $request     The WP_REST_Request object.
     *
     * @return  object|null
     */

    public function featuredImageData($object, $field_name, $request)
    {
        // Proceed if the post has a featured image.
        if (! empty($object['featured_media'])) {
            $image_id = (int)$object['featured_media'];
        } else {
            return null;
        }

        $image = get_post($image_id);

        if (! $image) {
            return null;
        }

        $featured_image['id']            = $image_id;
        $featured_image['alt_text']      = get_post_meta($image_id, '_wp_attachment_image_alt', true);
        $featured_image['caption']       = $image->post_excerpt;
        $featured_image['description']   = $image->post_content;
        $featured_image['media_type']    = wp_attachment_is_image($image_id) ? 'image' : 'file';
        $featured_image['post']          = ! empty($image->post_parent) ? (int) $image->post_parent : null;
        $featured_image['source_url']    = wp_get_attachment_url($image_id);

        return apply_filters('featured_image_data', $featured_image, $image_id);
    }
}
