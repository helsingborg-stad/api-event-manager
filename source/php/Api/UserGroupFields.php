<?php

namespace HbgEventImporter\Api;

/**
 * Adding meta fields to user groups taxonomy
 */

class UserGroupFields extends Fields
{
    private $taxonomy = 'user_groups';

    public function __construct()
    {
        add_action('rest_api_init', array($this, 'registerRestFields'));
    }

    /**
     * Return nested taxonomy children
     * @param   object  $object      The response object.
     * @param   string  $field_name  The name of the field to add.
     * @param   object  $request     The WP_REST_Request object.
     * @return  object|null
     */
    public function taxonomyChildren($object, $field_name, $request)
    {
        $children = array();
        $terms = get_terms($this->taxonomy, array('parent' =>  $object['id']));
        foreach ($terms as $key => $term) {
            $term_children = get_term_children($term->term_id, $this->taxonomy);
            $children[] = array("id" => $term->term_id, "name" => $term->name, "slug" => $term->slug);
            if ($term_children) {
                foreach ($term_children as $term_child_id) {
                    $term_child = get_term_by('id', $term_child_id, $this->taxonomy);
                    $children[$key]["children"][] = array("id" => $term_child->term_id, "name" => $term_child->name, "slug" => $term_child->slug);
                }
            } else {
                $children[$key]["children"] = null;
            }
        }
        $children = (! empty($children)) ? $children : null;
        return $children;
    }

    /**
     * Register rest fields to consumer api
     * @return  void
     * @version 0.3.2 creating consumer accessable meta values.
     */
    public static function registerRestFields()
    {
        // Location for the event
        register_rest_field($this->taxonomy,
            'children',
            array(
                'get_callback' => array($this, 'taxonomyChildren'),
                'update_callback' => null,
                'schema' => array(
                    'description' => 'Field containing object with location data.',
                    'type' => 'object',
                    'context' => array('view', 'embed')
                )
            )
        );
    }
}
