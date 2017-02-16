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
                'supports'             => array('title', 'revisions')
            )
        );
    }

    /**
     * Running filters connected to guide section of the api
     */
    public function runFilters()
    {
        add_filter('acf/update_value/name=guide_apperance_data', array($this, 'updateTaxonomyRelation'), 10, 3);
        add_filter('acf/load_field/name=guide_object_location', array($this, 'getSublocationsOnly'), 10, 3);
        add_filter('acf/fields/post_object/query/name=guide_main_location', array($this, 'getMainLocations'), 10, 3);
        add_action('wp_ajax_update_guide_sublocation_option', array($this, 'getSublocationsAjax'));
    }

    /**
     * Update taxonomy connection guide sender on save.
     * @param  $value     Value before save
     * @param  $post_id   Id of the post being saved or updated
     * @param  $field     Array containing field details
     */
    public function updateTaxonomyRelation($value, $post_id, $field)
    {
        wp_set_object_terms((int) $post_id, array((int) $value), 'guide_sender');
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
    public function getSublocationsOnly($field)
    {
        $parent_id = $this->getSelectedParent();

        if (!is_null($parent_id) && is_numeric($parent_id)) {
            $child_posts =  get_children($x = array(
                                'post_parent' => $parent_id,
                                'post_type'   => 'location',
                                'numberposts' => -1,
                                'post_status' => 'publish'
                            ));

            $field['choices'] = array('' => __("No location", 'event-manager'));

            if (is_array($child_posts)) {
                foreach ($child_posts as $item) {
                    $field['choices'][ $item->ID ] = $item->post_title . " (" . get_the_title($parent_id) . ")";
                }
            }
        }

        return $field;
    }

    public function getSelectedParent($postObject = null)
    {
        if (is_null($postObject)) {
            global $post;
        } else {
            $post = $postObject;
        }

        if (is_object($post) && isset($post->ID) && is_numeric($post->ID)) {
            return get_post_meta($post->ID, 'guide_main_location', true);
        }

        if (!is_object($post) && is_numeric($post)) {
            return get_post_meta($post, 'guide_main_location', true);
        }

        return false;
    }

    public function getSublocationsAjax()
    {
        $parent_id = (isset($_POST['selected']) && is_numeric($_POST['selected'])) ? $_POST['selected'] : null;

        if (!is_null($parent_id) && is_numeric($parent_id)) {
            $child_posts =  get_children(array(
                                'post_parent' => $parent_id,
                                'post_type'   => 'location',
                                'numberposts' => -1,
                                'post_status' => 'publish'
                            ));

            if (is_array($child_posts)) {
                $result = array('' => __("No location", 'event-manager'));
                foreach ($child_posts as $item) {
                    $result[ $item->ID ] = $item->post_title . " (" . get_the_title($parent_id) . ")";
                }

                echo json_encode($result);
                exit;
            }
        }

        echo json_encode(array('' => __("No location", 'event-manager')));
        exit;
    }
}
