<?php

namespace HbgEventImporter\PostTypes;

class Guides extends \HbgEventImporter\Entity\CustomPostType
{
    public function __construct()
    {
        $this->runFilters();

        parent::__construct(
            __('Guides', 'event-manager'),
            __('Guide', 'event-manager'),
            'guide',
            array(
                'description'          => 'Guided tours with beacon information',
                'menu_icon'            => 'data:image/svg+xml;base64,PD94bWwgdmVyc2lvbj0iMS4wIiBlbmNvZGluZz0iaXNvLTg4NTktMSI/PjxzdmcgeG1sbnM9Imh0dHA6Ly93d3cudzMub3JnLzIwMDAvc3ZnIiB4bWxuczp4bGluaz0iaHR0cDovL3d3dy53My5vcmcvMTk5OS94bGluayIgdmVyc2lvbj0iMS4xIiBpZD0iQ2FwYV8xIiB4PSIwcHgiIHk9IjBweCIgdmlld0JveD0iMCAwIDQ3OC4yOTcgNDc4LjI5NyIgc3R5bGU9ImVuYWJsZS1iYWNrZ3JvdW5kOm5ldyAwIDAgNDc4LjI5NyA0NzguMjk3OyIgeG1sOnNwYWNlPSJwcmVzZXJ2ZSIgd2lkdGg9IjUxMnB4IiBoZWlnaHQ9IjUxMnB4Ij48Zz48Zz48cGF0aCBkPSJNNDI1LjI5OCwyOC45M2wtMTIxLjU1LDc4LjJMMTgxLjkxNSwzLjk5NmMtNS45NS00LjgxNy0xNC4xNjctNS4zODMtMjAuNjgzLTEuMTMzTDM0LjAxNSw4OS44NDYgICAgYy00LjUzMywzLjExNy03LjM2Nyw4LjUtNy4zNjcsMTQuMTY3VjQxOS45M2MwLDYuMjMzLDMuNCwxMi4xODMsOS4wNjcsMTUuMDE3czEyLjE4MywyLjU1LDE3LjU2Ny0xLjEzM2wxMDcuMzgzLTczLjM4MyAgICBsMTMwLjA1LDExMy42MTdjMy4xMTcsMi44MzMsNy4wODMsNC4yNSwxMS4wNSw0LjI1YzMuMTE3LDAsNS45NS0wLjg1LDguNzgzLTIuNTVsMTMyLjg4My03OS42MTdjNS4xLTMuMTE3LDguMjE3LTguNSw4LjIxNy0xNC40NSAgICBWNDMuMDk2YzAtNi4yMzMtMy40LTExLjktOC43ODMtMTUuMDE3QzQzNy4xOTgsMjUuMjQ2LDQzMC42ODIsMjUuNTMsNDI1LjI5OCwyOC45M3ogTTQxNy42NDgsMzcyLjMzbC0xMTMuOSw2OC4yODMgICAgbC0xMjQuMS0xMDguNTE3di02NC4wMzNjMC05LjM1LTcuNjUtMTctMTctMTdjLTkuMzUsMC0xNyw3LjY1LTE3LDE3djYxLjc2N2wtODUsNTguMDgzVjExMy4wOGwxMDkuMDgzLTc0LjUxN2wxMTQuNzUsOTYuNjE3ICAgIHYyMTUuMDVjMCw5LjM1LDcuNjUsMTcsMTcsMTdzMTctNy42NSwxNy0xN1YxMzguMjk2bDk5LjE2Ny02NC4wMzNWMzcyLjMzeiIgZmlsbD0iI0ZGRkZGRiIvPjxwYXRoIGQ9Ik0yMjEuODY1LDExMS42NjNjLTYuNTE3LTYuNTE3LTE3LjI4My02LjUxNy0yNC4wODMsMGwtMjguOSwyOC45bC0yOC45LTI4LjljLTYuNTE3LTYuNTE3LTE3LjI4My02LjUxNy0yNC4wODMsMCAgICBjLTYuNTE3LDYuNTE3LTYuNTE3LDE3LjI4MywwLDI0LjA4M2wyOC45LDI4LjlsLTI4LjksMjkuMTgzYy02LjUxNyw2LjUxNy02LjUxNywxNy4yODMsMCwyNC4wODNjMy40LDMuNCw3LjY1LDUuMSwxMS45LDUuMSAgICBjNC4yNSwwLDguNzgzLTEuNywxMS45LTUuMWwyOS4xODMtMjkuMTgzbDI4LjksMjguOWMzLjQsMy40LDcuNjUsNS4xLDExLjksNS4xczguNzgzLTEuNywxMS45LTUuMSAgICBjNi41MTctNi41MTcsNi41MTctMTcuMjgzLDAtMjQuMDgzbC0yOC42MTctMjguOWwyOC45LTI4LjlDMjI4LjY2NSwxMjguOTQ2LDIyOC42NjUsMTE4LjE4LDIyMS44NjUsMTExLjY2M3oiIGZpbGw9IiNGRkZGRkYiLz48L2c+PC9nPjxnPjwvZz48Zz48L2c+PGc+PC9nPjxnPjwvZz48Zz48L2c+PGc+PC9nPjxnPjwvZz48Zz48L2c+PGc+PC9nPjxnPjwvZz48Zz48L2c+PGc+PC9nPjxnPjwvZz48Zz48L2c+PGc+PC9nPjwvc3ZnPg==',
                'public'               => true,
                'publicly_queriable'   => true,
                'show_ui'              => true,
                'show_in_nav_menus'    => true,
                'has_archive'          => true,
                'rewrite'              => array(
                    'slug'       => 'guide',
                    'with_front' => false
                ),
                'hierarchical'         => false,
                'exclude_from_search'  => true,
                'supports'             => array('title', 'revisions'),
                'map_meta_cap'         => true,
                'capability_type'      => 'guide',
            )
        );
    }

    /**
     * Running filters connected to guide section of the api
     */
    public function runFilters()
    {

        //Update taxonomy
        add_filter('acf/update_value/name=guidegroup', array($this, 'updateTaxonomyRelation'), 10, 3);

        //Only main locations selectable
        //add_filter('acf/fields/post_object/query/name=guide_taxonomy_location', array($this, 'getMainLocations'), 10, 3);

        //Only sublocations selectable (if set)
        add_filter('acf/fields/post_object/query/key=field_58ab0c9554b0a', array($this, 'getSublocationsOnly'), 10, 3);

        //Objects
        add_filter('acf/load_field/key=field_58ab0cf054b0b', array($this, 'getPostObjects'), 10, 1);

        //Pad exhibition id on save
        add_filter('acf/update_value/key=field_589dcc1a7deb3', array($this, 'padExhibitionID'), 10, 3);
    }

    /**
     * Pad the exhibition id
     * @param  $value     Value before save
     * @param  $post_id   Id of the post being saved or updated
     * @param  $field     Array containing field details
     */

    public function padExhibitionID($value, $post_id, $field)
    {
        return str_pad((string) $value, 3, "0", STR_PAD_LEFT);
    }

    /**
     * Filter objects
     * @param  $posts   List of posts
     * @param  $query   Id of the post being saved or updated
     */
    public function objects($posts, $query)
    {
        if (!defined('DOING_AJAX') || !DOING_AJAX || $query->query['post_type'] !== 'acf-field' || !isset($_POST['objects'])) {
            return $posts;
        }

        return $posts;
    }

    /**
     * Update taxonomy connection guide sender on save.
     * @param  $value     Value before save
     * @param  $post_id   Id of the post being saved or updated
     * @param  $field     Array containing field details
     */
    public function updateTaxonomyRelation($value, $post_id, $field)
    {
        wp_set_object_terms((int) $post_id, array((int) $value), 'guidegroup');
        return $value;
    }

    /**
     * Only get main locations containing childrens
     * @param  $field     Array containing field details
     */
    public function getMainLocations($args, $field, $post_id)
    {
        global $wpdb;
        $valid_id = $wpdb->get_col("SELECT post_parent FROM {$wpdb->posts} WHERE post_parent != 0 AND post_type='location' GROUP BY post_parent");

        if (is_array($valid_id)) {
            $args['post__in'] = $valid_id;
        }

        return $args;
    }

    /**
     * Only get sublocation to previously selected main location.
     * @param  $field     Array containing field details
     */
    public function getPostObjects($field)
    {
        global $post, $typenow;

        // Bail if acf group edit
        if ((isset($post->post_type) && $post->post_type == 'acf-field-group') || $typenow == 'acf-field-group') {
            return $field;
        }

        if (is_object($post) && isset($post->ID)) {
            $postId = $post->ID;
        } elseif(isset($_REQUEST['post_id']) && is_numeric($_REQUEST['post_id'])) {
            $postId = $_REQUEST['post_id'];
        } else {
            return;
        }

        $field['choices'] = [];

        foreach ((array) get_field('guide_content_objects', $postId) as $key => $item) {
            if (!empty($item['guide_object_id'])) {
                $field['choices'][$item['guide_object_uid']] = $item['guide_object_title'] . " (" . $item['guide_object_id'] . ")";
            } else {
                $field['choices'][$item['guide_object_uid']] = $item['guide_object_title'];
            }
        }

        return $field;
    }

    /**
     * Only get sublocation to previously selected main location.
     * @param  $field     Array containing field details
     */
    public function getSublocationsOnly($args, $field, $post_id)
    {
        if (isset($_POST['selectedGroup']) && !empty($_POST['selectedGroup'])) {
            $location = get_field('guide_taxonomy_location', 'guidegroup_' . $_POST['selectedGroup']);
            $onlySub = get_field('guide_taxonomy_sublocations', 'guidegroup_' . $_POST['selectedGroup']);

            if ($onlySub) {
                $args['post_parent'] = $location;
            }
        }

        return $args;
    }
}
