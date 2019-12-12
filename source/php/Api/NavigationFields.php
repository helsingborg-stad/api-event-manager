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
    }

    /**
     * Register rest fields to consumer api
     * @return  void
     */
    public function registerRestFields()
    {
        register_rest_field($this->taxonomy,
            'layout',
            array(
                'get_callback'    => array($this, 'getSingleTaxMetaCallback'),
                'update_callback' => null,
                'schema'          => null,
            )
        );

        register_rest_field($this->taxonomy,
            'object_list',
            array(
                'get_callback'    => array($this, 'combineGuideOrganisation'),
                'update_callback' => null,
                'schema'          => null,
            )
        );
    }

    /**
     * Combine taxonomy and post format in one array
     * @return array
     */

    public function combineGuideOrganisation($object, $field_name, $request)
    {
        $result = array();

        $postRelations = $this->getGuideNavigationPostRelations((object) $object);

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

        $recommendationRelations = $this->getRecommendationNavigationPostRelations((object) $object);

        if (!empty($recommendationRelations) && is_array($recommendationRelations)) {
            foreach ($recommendationRelations as $recommendationRelation) {
                $result[] = array('id' => $recommendationRelation, 'type' => 'recommendation');
            }
        }

        return $result;
    }

    /**
     * Get connected guides
     * @return array
     */
    public function getGuideNavigationPostRelations($taxonomy)
    {
        $result = array();

        if (!get_field('include_specific_posts', $taxonomy->taxonomy. '_' . $taxonomy->id)) {
            $posts = get_posts(array(
                'post_type' => 'guide',
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
            $related = get_field('included_posts', $taxonomy->taxonomy. '_' . $taxonomy->id);

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
     * Get connected recommendations
     * @return array
     */
    public function getRecommendationNavigationPostRelations($taxonomy)
    {
        $result = array();

        if (!get_field('include_specific_recommendations', $taxonomy->taxonomy. '_' . $taxonomy->id)) {
            $posts = get_posts(array(
                'post_type' => 'recommendation',
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
            $related = get_field('included_recommendations', $taxonomy->taxonomy. '_' . $taxonomy->id);

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
