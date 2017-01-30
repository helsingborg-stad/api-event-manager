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
     * Set current occasion when getting multiple events.
     * @return object
     */
    public function setCurrentOccasion( $data, $post, $context ) {
        $start_date = (! empty($post->timestamp_start)) ? date('Y-m-d H:i', $post->timestamp_start) : null;
        $end_date = (! empty($post->timestamp_end)) ? date('Y-m-d H:i', $post->timestamp_end) : null;
        $door_time = (! empty($post->timestamp_door)) ? date('Y-m-d H:i', $post->timestamp_door) : null;

        if (! empty ($data->data['occasions'])) {
            foreach ($data->data['occasions'] as $key => $val) {
                if ($val['start_date'] == $start_date && $val['end_date'] == $end_date && $val['door_time'] == $door_time) {
                    $data->data['occasions'][$key]['current_occasion'] = true;
                }
            }
        }
        return $data;
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

        // Get upcoming occasions
        $timestamp = strtotime("midnight now") - 1;
        $query =
        "
        SELECT * FROM $db_occasions
        WHERE event = $id
        AND timestamp_end > $timestamp
        ";
        $query_results = $wpdb->get_results($query, OBJECT);

        // Get and save occasions from post meta, to get complete data
        $return_value = self::getFieldGetMetaData($object, 'occasions', $request);
        if (is_array($return_value)||is_object($return_value) && !empty($return_value)) {
            foreach ($return_value as $key => $value) {
                // Skip passed occasions
                if (strtotime($value['end_date']) < $timestamp) {
                    continue;
                }
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
        // Remove duplicates from $data array
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

        $featured_image['ID']            = $image_id;
        $featured_image['alt_text']      = get_post_meta($image_id, '_wp_attachment_image_alt', true);
        $featured_image['caption']       = $image->post_excerpt;
        $featured_image['description']   = $image->post_content;
        $featured_image['media_type']    = wp_attachment_is_image($image_id) ? 'image' : 'file';
        $featured_image['post']          = ! empty($image->post_parent) ? (int) $image->post_parent : null;
        $featured_image['source_url']    = wp_get_attachment_url($image_id);

        return apply_filters('featured_image_data', $featured_image, $image_id);
    }

    /**
     * Add data / meta data to organizers field.
     *
     * @param   object  $object      The response object.
     * @param   string  $field_name  The name of the field to add.
     * @param   object  $request     The WP_REST_Request object.
     *
     * @return  object|null
     */
    public function organizerData($object, $field_name, $request)
    {
        $return_value = self::getFieldGetMetaData($object, $field_name, $request);

        if (is_array($return_value) || is_object($return_value) && !empty($return_value)) {
            $organizers = $return_value;
        } else {
            return null;
        }

        $organizers_arr = array();
        foreach ($organizers as $organizer) {
            $contacts_array = array();
            if (! empty($organizer['contacts']) && is_array($organizer['contacts'])) {
                foreach ($organizer['contacts'] as $contact) {
                    $contacts_array[] = array(
                    'title' => get_the_title($contact->ID),
                    'name' => get_field('name', $contact->ID),
                    'phone_number' => get_field('phone_number', $contact->ID),
                    'email' => get_field('email', $contact->ID),
                    );
                }
            $organizer['contacts'] = $contacts_array;
            }
            $organizers_arr[] = $organizer;
        }
        return $organizers_arr;
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

        $location = get_post($location_id);

        if (! $location) {
            return null;
        }

        $location_data['ID']                = $location_id;
        $location_data['title']             = $location->post_title;
        $location_data['content']           = $location->post_content;
        $location_data['street_address']    = get_post_meta($location_id, 'street_address', true);
        $location_data['postal_code']       = get_post_meta($location_id, 'postal_code', true);
        $location_data['city']              = get_post_meta($location_id, 'city', true);
        $location_data['country']           = get_post_meta($location_id, 'country', true);
        $location_data['formatted_address'] = get_post_meta($location_id, 'formatted_address', true);
        $location_data['latitude']          = get_post_meta($location_id, 'latitude', true);
        $location_data['longitude']         = get_post_meta($location_id, 'longitude', true);

        return $location_data;
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
    public function additionalLocationData($object, $field_name, $request)
    {
        $return_value = self::getFieldGetMetaData($object, $field_name, $request);

        if (is_array($return_value) || is_object($return_value) && !empty($return_value)) {
            $locations = $return_value;
        } else {
            return null;
        }

        $location_arr = array();
        foreach ($locations as $location) {
            $location = get_post($location);

            if (! $location) {
                continue;
            }

            $location_arr[] = array(
                'ID'                => $location->ID,
                'title'             => $location->post_title,
                'content'           => $location->post_content,
                'street_address'    => get_post_meta($location->ID, 'street_address', true),
                'postal_code'       => get_post_meta($location->ID, 'postal_code', true),
                'city'              => get_post_meta($location->ID, 'city', true),
                'country'           => get_post_meta($location->ID, 'country', true),
                'formatted_address' => get_post_meta($location->ID, 'formatted_address', true),
                'latitude'          => get_post_meta($location->ID, 'latitude', true),
                'longitude'         => get_post_meta($location->ID, 'longitude', true),
            );
        }

        return $location_arr;
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

        $taxArray = array();
        foreach ($taxonomies as $val) {
            $term = get_term($val, $field_name);
            $taxArray[] .= $term->name;
        }

        return apply_filters($object['type'] . '_taxonomies', $taxArray);
    }

    /**
     * Replace id with array with group id, name and slug
     *
     * @param   object  $object      The response object.
     * @param   string  $field_name  The name of the field to add.
     * @param   object  $request     The WP_REST_Request object.
     *
     * @return  object|null
     */

    public function userGroups($object, $field_name, $request)
    {
        if (! empty($object[$field_name])) {
            $taxonomies = $object[$field_name];
        } else {
            return null;
        }

        $taxArray = array();
        foreach ($taxonomies as $val) {
            $term = get_term($val, $field_name);
            $taxArray[] = array(
                            'id'    => $term->term_id,
                            'name'  => $term->name,
                            'slug'  => $term->slug
                        );
        }

        return apply_filters($object['type'] . '_taxonomies', $taxArray);
    }

}
