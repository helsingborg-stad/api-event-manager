<?php

namespace HbgEventImporter\Api;

/**
 * Adding meta fields to location post type
 */

class NavigationFields extends Fields
{
    private $taxonomy = 'navigation';

    public function __construct()
    {
        add_action('rest_api_init', array($this, 'registerRestFields'));
        add_filter('rest_navigation_query', array($this, 'addUserGroupFilter'), 10, 2);
    }

    /**
     * Register rest fields to consumer api
     * @return  void
     */
    public function registerRestFields()
    {
        register_rest_field(
            $this->taxonomy,
            'layout',
            array(
                'get_callback'    => array($this, 'getSingleTaxMetaCallback'),
                'update_callback' => null,
                'schema'          => null,
            )
        );

        register_rest_field(
            $this->taxonomy,
            'object_list',
            array(
                'get_callback'    => array($this, 'combineGuideOrganisation'),
                'update_callback' => null,
                'schema'          => null,
            )
        );

        register_rest_field(
            $this->taxonomy,
            'user_groups',
            array(
            'get_callback' => array($this, 'getTaxonomyTerm'),
            'update_callback' => null,
            'schema' => null,
          )
        );
    }

    /**
      * Filter by group id
      *
      * @param  array           $args    The query arguments.
      * @param  WP_REST_Request $request Full details about the request.
      * @return array $args.
      */
    public function addUserGroupFilter($args, $request)
    {
        if ($groupId = $request->get_param('group-id')) {
            $args['meta_key'] = 'user_groups';
            $args['meta_value'] = intval($groupId);
        }

        return $args;
    }

    /**
     * Get taxonomy term
     * @return array
     */
    public function getTaxonomyTerm($object, $field_name, $request)
    {
        $taxonomy = get_field($field_name, $this->taxonomy . '_' . $object['id']);
        if (empty($taxonomy)) {
            return null;
        }
        // Collect term data
        $term = get_term($taxonomy, $field_name);
        // Create value array
        $termData = array(
          'id'    => $term->term_id ?? null,
          'name'  => $term->name ?? null,
          'slug'  => $term->slug ?? null
        );

        return $termData;
    }

    /**
     * Combine taxonomy and post format in one array
     * @return array
     */

    public function combineGuideOrganisation($object, $field_name, $request)
    {
        $result = array();

        $postRelations = $this->getPosttypeNavigationPostRelations((object) $object, 'guide');

        if (!empty($postRelations) && is_array($postRelations)) {
            foreach ($postRelations as $postRelation) {
                $result[] = array('id' => $postRelation, 'type' => 'guide');
            }
        }

        $taxonomyRelations = $this->getGuideNavigationTaxonomyRelations((object) $object);

        if (!empty($taxonomyRelations) && is_array($taxonomyRelations)) {
            foreach ($taxonomyRelations as $taxonomyRelation) {
                $result[] = array('id' => $taxonomyRelation, 'type' => 'guidegroup');
            }
        }

        $recommendationRelations = $this->getPosttypeNavigationPostRelations((object) $object, 'recommendation');
        error_log(print_r($recommendationRelations, true));

        if (!empty($recommendationRelations) && is_array($recommendationRelations)) {
            foreach ($recommendationRelations as $recommendationRelation) {
                $result[] = array('id' => $recommendationRelation, 'type' => 'recommendation');
            }
        }

        $interactiveGuidesRelations = $this->getPosttypeNavigationPostRelations((object) $object, 'interactive_guide');

        if (!empty($interactiveGuidesRelations) && is_array($interactiveGuidesRelations)) {
            foreach ($interactiveGuidesRelations as $interactiveGuidesRelation) {
                $result[] = array('id' => $interactiveGuidesRelation, 'type' => 'interactive_guide');
            }
        }

        return $result;
    }

    /**
     * Get connected post types
     * @return array
     */
    public function getPosttypeNavigationPostRelations($taxonomy, $postType)
    {
        $result = array();

        $postTypePlural = $postType . 's';

        if (!get_field('include_specific_' . $postTypePlural, $taxonomy->taxonomy. '_' . $taxonomy->id)) {
            $posts = get_posts(array(
                'post_type' => $postType,
                'posts_per_page' => -1,
            ));

            if (!empty($posts) && is_array($posts)) {
                foreach ($posts as $relation) {
                    if (!isset($relation->ID)) {
                        continue;
                    }

                    $result[] = $relation->ID;
                }
            }
        } else {
            $related = get_field('included_' . $postTypePlural, $taxonomy->taxonomy. '_' . $taxonomy->id);
            error_log($taxonomy->taxonomy. '_' . $taxonomy->id);
            error_log(print_r($related, true));

            if (!is_null($related) && is_array($related) && !empty($related)) {
                foreach ($related as $relation) {
                    if (!isset($relation->ID)) {
                        continue;
                    }

                    $result[] = $relation->ID;
                }
            }
        }

        return $result;
    }

    /**
     * Get connected organisations
     * @return array
     */
    public function getGuideNavigationTaxonomyRelations($taxonomy)
    {
        $result = array();

        if (!get_field('include_specific_taxonomys', $taxonomy->taxonomy. '_' . $taxonomy->id)) {
            $terms = get_terms(array('taxonomy' => 'guidegroup'));

            if (!empty($terms) && is_array($terms)) {
                foreach ($terms as $relation) {
                    if (!isset($relation->term_id)) {
                        continue;
                    }

                    $result[] = $relation->term_id;
                }
            }
        } else {
            $related = get_field('included_taxonomys', $taxonomy->taxonomy. '_' . $taxonomy->id);

            if (!is_null($related) && is_array($related) && !empty($related)) {
                foreach ($related as $relation) {
                    if (!isset($relation->term_id)) {
                        continue;
                    }

                    $result[] = $relation->term_id;
                }
            }
        }

        return $result;
    }
}
