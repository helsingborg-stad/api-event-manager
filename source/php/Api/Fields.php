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
     * Returning a numeric value formatted by acf if exist
     * @return  int, float, null
     * @version 0.3.0 creating consumer accessable meta values.
     */
    public function boolGetCallBack($object, $field_name, $request, $formatted = true)
    {
        $return_value = self::getFieldGetMetaData($object, $field_name, $request);

        if (is_numeric($return_value) ||is_bool($return_value)) {
            return (bool) $return_value;
        } else {
            return false;
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
        $return_value = self::getFieldGetMetaData($object, $field_name, $request, $formatted);

        if (is_string($return_value) && $return_value !== '') {
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
     * Update acf repeater field in database
     * @return  bool
     * @version 0.3.0 creating consumer accessable meta values.
     */
    public function acfUpdateCallBack($value, $object, $field_name)
    {
        global $wpdb;

        if (! $value || ! is_object($value) && ! is_array($value)) {
            return;
        }

        // Get acf field key
        $field_name = esc_sql("_".$field_name);
        $field_key = $wpdb->get_results("SELECT meta_value FROM $wpdb->postmeta WHERE $wpdb->postmeta.meta_key = '$field_name' ORDER BY meta_id DESC LIMIT 1", ARRAY_A);
        $key = $field_key[0]['meta_value'];

        if (preg_match('/field_[0-9]+/', $key)) {
            return update_field($key, $value, $object->ID);
        } else {
            return;
        }
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
     * Replace id with taxonomy name
     *
     * @param   object  $object      The response object.
     * @param   string  $field_name  The name of the field to add.
     * @param   object  $request     The WP_REST_Request object.
     *
     * @return  object|null
     */
    public function renameTaxonomies($object, $field_name, $request)
    {
        if (! empty($object[$field_name])) {
            $taxonomies = $object[$field_name];
        } else {
            return null;
        }

        foreach ($taxonomies as &$val) {
            $term = get_term($val, $field_name);
            $val  = $term->name;
        }

        return apply_filters($object['type'] . '_taxonomies', $taxonomies);
    }

    /**
     * Replace id with array with taxonomy id, name and slug
     *
     * @param   object  $object      The response object.
     * @param   string  $field_name  The name of the field to add.
     * @param   object  $request     The WP_REST_Request object.
     *
     * @return  object|null
     */
    public function getTaxonomyCallback($object, $field_name, $request)
    {
        if (! empty($object[$field_name])) {
            $taxonomies = $object[$field_name];
        } else {
            return null;
        }

        foreach ($taxonomies as &$val) {
            $term = get_term($val, $field_name);
            $val = array(
                            'id'    => $term->term_id,
                            'name'  => $term->name,
                            'slug'  => $term->slug
                        );
        }

        return apply_filters($object['type'] . '_taxonomies', $taxonomies);
    }

    /**
     * Add data / meta data to additional locations field.
     *
     * @param   object  $object      The response object.
     * @param   string  $field_name  The name of the field to add.
     * @param   object  $request     The WP_REST_Request object.
     *
     * @return  object|null
     */
    public function locationData($object, $field_name, $request)
    {
        $return_value = self::getFieldGetMetaData($object, $field_name, $request);

        if (is_int($return_value) || is_string($return_value) && ! empty($return_value)) {
            $location_id = intval($return_value);
        } else {
            return null;
        }

        return $this->getLocationData($location_id);
    }

    /**
     * Gather locaton data
     *
     * @param [int] $id Location ID
     * @return void
     */
    public function getLocationData($id)
    {
        $location = get_post($id);

        if (! $location) {
            return null;
        }

        $parent = ! empty($location->post_parent) ? array('id' => $location->post_parent, 'title' => get_the_title($location->post_parent)) : null;
        $location_data['id']                = $id;
        $location_data['title']             = $location->post_title;
        $location_data['parent']            = $parent;
        $location_data['content']           = $location->post_content;
        $location_data['street_address']    = get_post_meta($id, 'street_address', true);
        $location_data['postal_code']       = get_post_meta($id, 'postal_code', true);
        $location_data['city']              = get_post_meta($id, 'city', true);
        $location_data['country']           = get_post_meta($id, 'country', true);
        $location_data['formatted_address'] = get_post_meta($id, 'formatted_address', true);
        $location_data['latitude']          = get_post_meta($id, 'latitude', true);
        $location_data['longitude']         = get_post_meta($id, 'longitude', true);

        return $location_data;
    }

    /**
     * Return plaintext field for posts
     *
     * @param   object  $object      The response object.
     * @param   string  $field_name  The name of the field to add.
     * @param   object  $request     The WP_REST_Request object.
     *
     * @return  object|null
     */
    public function addPlaintextField($object, $field_name, $request) {
        $object[$field_name]['plain_text'] = strip_tags(html_entity_decode($object[$field_name]['rendered']));
        return $object[$field_name];
    }

    /**
     * Adds additional data to featured_media field.
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

    /**
     * Returning a single taxonomy meta field
     * @param   object  $object      The response object.
     * @param   string  $field_name  The name of the field to add.
     * @param   object  $request     The WP_REST_Request object.
     * @return mixed
     */
    public function getSingleTaxMetaCallback($object, $field_name, $request)
    {
        return isset($object['id']) ? get_term_meta($object['id'], $field_name, true) : null;
    }
}
