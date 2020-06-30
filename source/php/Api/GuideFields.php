<?php

namespace HbgEventImporter\Api;

/**
 * Adding meta fields to location post type
 */

class GuideFields extends Fields
{
    private $postType       = 'guide';
    private $taxonomyName   = 'guidegroup';
    private $objectCache    = array();

    public function __construct()
    {

        //Fields
        add_action('rest_api_init', array($this, 'registerRestFields'));
        add_action('rest_api_init', array($this, 'registerTaxonomyRestFields'));

        //Api filter querys
        add_filter('rest_guide_query', array($this, 'addUserGroupFilter'), 10, 2);
        add_filter('rest_prepare_guide', array($this, 'addObjectFilter'), 6000, 3);
    }

    /**
     * Filter by object
     * @param  array           $args    The query arguments.
     * @param  WP_REST_Request $request Full details about the request.
     * @return array $response.
     **/
    public function addObjectFilter($response, $post, $request)
    {
        if (isset($_GET['object'])) {

            //Parse content object
            if (isset($response->data['contentObjects'][$_GET['object']])) {
                $response->data['contentObject'] = $response->data['contentObjects'][$_GET['object']];
            } else {
                $response->data['contentObject'] = null;
            }

            //Define allowed keys
            $keys = array('contentObject');

            //Do filtering
            $response->data = array_filter($response->data, function ($k) use ($keys) {
                return in_array($k, $keys, true);
            }, ARRAY_FILTER_USE_KEY);
        }

        return $response;
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
            $args['meta_value'] = sprintf(':"%s";', $groupId);
            $args['meta_compare'] = 'LIKE';
        }

        return $args;
    }

    /**
     * Register rest fields to consumer api
     * @return  void
     * @version 0.3.28 creating consumer accessable meta values.
     */
    public function registerTaxonomyRestFields()
    {
        register_rest_field($this->taxonomyName,
            'apperance',
            array(
                'get_callback' => array($this, 'taxonomyApperance'),
                'schema' => array(
                    'description' => 'Describes the guides colors, logo and mood image.',
                    'type' => 'object',
                    'context' => array('view', 'embed')
                )
            )
        );

        register_rest_field($this->taxonomyName,
            'settings',
            array(
                'get_callback' => array($this, 'taxonomySettings'),
                'schema' => array(
                    'description' => 'Describes  main location in generel terms.',
                    'type' => 'object',
                    'context' => array('view', 'embed')
                )
            )
        );

        register_rest_field($this->taxonomyName,
            'notice',
            array(
                'get_callback' => array($this, 'taxonomyNotice'),
                'schema' => array(
                    'description' => 'Describes main location in generel terms.',
                    'type' => 'object',
                    'context' => array('view', 'embed')
                )
            )
        );
    }

    /**
     * Register rest fields to consumer api
     * @return  void
     * @version 0.3.28 creating consumer accessable meta values.
     */
    public function registerRestFields()
    {

        // Embed type of guide as property
        register_rest_field($this->postType,
            'content_type',
            array(
                'get_callback'    => array($this, 'stringGetCallBack'),
                'update_callback' => null,
                'schema'          => null,
            )
        );

        // Add a tagline datafield
        register_rest_field($this->postType,
            'guide_tagline',
            array(
                'get_callback'    => array($this, 'stringGetCallBack'),
                'update_callback' => null,
                'schema'          => null,
            )
        );

        // When does the guide start?
        register_rest_field($this->postType,
            'guide_date_start',
            array(
                'get_callback'    => array($this, 'stringGetCallBack'),
                'update_callback' => null,
                'schema'          => null,
            )
        );

        // When does the guide end?
        register_rest_field($this->postType,
            'guide_date_end',
            array(
                'get_callback'    => array($this, 'stringGetCallBack'),
                'update_callback' => null,
                'schema'          => null,
            )
        );

        // Title as plain text
        register_rest_field($this->postType,
            'title',
            array(
                'get_callback'    => array($this, 'addPlaintextField'),
                'update_callback' => null,
                'schema'          => null,
            )
        );

        //Main content object
        register_rest_field($this->postType,
            'content',
            array(
                'get_callback' => array($this, 'addDescription'),
                'schema' => array(
                    'description' => 'The guide main description of the guide.',
                    'type' => 'object',
                    'context' => array('view', 'embed')
                )
            )
        );

        // Guide media objects
        register_rest_field($this->postType,
            'guide_kids',
            array(
                'get_callback' => array($this, 'boolGetCallBack'),
                'update_callback' => array($this, 'objectUpdateCallBack'),
                'schema' => array(
                    'description' => 'Guide main location object id.',
                    'type' => 'object',
                    'context' => array('view', 'embed')
                )
            )
        );

        // Replace group id with taxonomy name
        register_rest_field($this->postType,
            'guidegroup',
            array(
                'get_callback' => array($this, 'getTaxonomyCallback'),
                'update_callback' => array($this, 'objectUpdateCallBack'),
                'schema' => array(
                    'description' => 'Describes main location in generel terms.',
                    'type' => 'object',
                    'context' => array('view', 'embed')
                )
            )
        );

        // Replace group id with taxonomy name
        register_rest_field($this->postType,
            'user_groups',
            array(
                'get_callback' => array($this, 'getTaxonomyCallback'),
                'update_callback' => array($this, 'objectUpdateCallBack'),
                'schema' => null
            )
        );

        // Guide media objects
        register_rest_field($this->postType,
            'guide_location',
            array(
                'get_callback' => array($this, 'numericGetCallBack'),
                'update_callback' => array($this, 'objectUpdateCallBack'),
                'schema' => array(
                    'description' => 'Guide main location object id.',
                    'type' => 'object',
                    'context' => array('view', 'embed')
                )
            )
        );

        // Guide media objects
        register_rest_field($this->postType,
            'guide_images',
            array(
                'get_callback' => array($this, 'objectGetCallBack'),
                'update_callback' => array($this, 'objectUpdateCallBack'),
                'schema' => array(
                    'description' => 'Guide main media information.',
                    'type' => 'object',
                    'context' => array('view', 'embed')
                )
            )
        );

        register_rest_field($this->postType,
            'content_objects',
            array(
                'get_callback' => array($this, 'postObjects'),
                'schema' => array(
                    'description' => 'Objects of this guide.',
                    'type' => 'object',
                    'context' => array('view', 'embed')
                )
            )
        );

        register_rest_field($this->postType,
            'sub_attractions',
            array(
                'get_callback' => array($this, 'subAttractionBeacons'),
                'schema' => array(
                    'description' => 'Describes the guides colors, logo, moodimage and main location.',
                    'type' => 'object',
                    'context' => array('view', 'embed')
                )
            )
        );

        register_rest_field($this->postType,
            'orphan_content_objects',
            array(
                'get_callback' => array($this, 'orphanPostObjects'),
                'schema' => array(
                    'description' => 'Objects of this guide.',
                    'type' => 'object',
                    'context' => array('view', 'embed')
                )
            )
        );
    }

    /* TAXONOMY */

    /**
     * Create a ACF taxonomy id from taxonomy object
     * @return  void
     * @version 0.3.28 Guides
     */

    private function taxonomyKey($taxonomy)
    {
        return $taxonomy['taxonomy']. '_' . $taxonomy['id'];
    }

    /**
     * Convert value to noll
     * @return  null or initial provided value
     * @version 0.3.28 Guides
     */

    private function convertToNull($value, $convertValueToNull = false)
    {
        if ($value == $convertValueToNull) {
            return null;
        }
        return $value;
    }

    /**
     * Create apperance response array
     * @return  array
     * @version 0.3.28 Guides
     */

    public function taxonomyApperance($object, $field_name, $request, $formatted = true)
    {
        return array(
            'logotype'          => $this->convertToNull(get_field('guide_taxonomy_logotype', $this->taxonomyKey($object))),
            'color'             => $this->convertToNull(get_field('guide_taxonomy_color', $this->taxonomyKey($object))),
            'image'             => $this->convertToNull(get_field('guide_taxonomy_image', $this->taxonomyKey($object)))
        );
    }

    /**
     * Create   Create taxonomy response array
     * @return  array
     * @version 0.3.28 Guides
     */

    public function taxonomySettings($object, $field_name, $request, $formatted = true)
    {
        return array(
            'active'            => get_field('guide_taxonomy_active', $this->taxonomyKey($object)),
            'location'          => $this->convertToNull(get_field('guide_taxonomy_location', $this->taxonomyKey($object))),
            'locationRadius'    => $this->convertToNull(get_field('guide_taxonomy_radius', $this->taxonomyKey($object))),
            'locationFilter'    => get_field('guide_taxonomy_sublocations', $this->taxonomyKey($object)),
            'wifi'              => get_field('guide_taxonomy_wifi', $this->taxonomyKey($object)),
            'map'               => get_field('guide_taxonomy_map', $this->taxonomyKey($object))
        );
    }

    /**
     * Create taxonomy/notice response array
     * @return  void
     * @version 0.3.28 Guides
     */

    public function taxonomyNotice($object, $field_name, $request, $formatted = true)
    {
        return array(
            'arrival'           => $this->convertToNull(get_field('guide_arrival_notice', $this->taxonomyKey($object))),
            'departure'         => $this->convertToNull(get_field('guide_departure_notice', $this->taxonomyKey($object))),
        );
    }

    /**
     * Create array of sub attraction beacons
     * @return  array
     * @version 0.3.28 Guides
     */

    public function subAttractionBeacons($object, $field_name, $request, $formatted = true)
    {
        $result             = array();
        $beacons            = $this->objectGetCallBack($object, 'guide_beacon', $request, true);
        $objects            = $this->getObjects($object, 'guide_content_objects', $request, true);

        if (!$beacons) {
            return null;
        }

        foreach ($beacons as $key => $item) {
            if (!empty($item['objects'])) {
                if (is_string($item['objects'])) {
                    $item['objects'] = explode("||", $item['objects']);
                }

                if (is_string($item['objects'])) {
                    $item['objects'] = explode(",", $item['objects']);
                }

                if (!empty($item['objects'])) {
                    $result[] = array(
                        'order' => $key,
                        'bid' => $item['beacon'],
                        'beacon_distance' => $item['distance'],
                        'content' => $item['objects'],
                        'location' => is_numeric($item['location']) ? $item['location'] : null
                    );
                }
            }
        }

        if (!array_filter($result)) {
            return null;
        }

        return $result;
    }

    /**
     * Create list of objects not contained in a subattraction
     * @return  array
     * @version 0.3.28 Guides
     */

    public function orphanPostObjects($object, $field_name, $request, $formatted = true)
    {
        $result             = array();
        $objectStash        = array();
        $baconStash         = array();

        $beacons            = $this->objectGetCallBack($object, 'guide_beacon', $request, true);
        $objects            = $this->getObjects($object, 'guide_content_objects', $request, true);
        if (! $beacons) {
            return null;
        }
        //Create total objects
        foreach ($objects as $key => $item) {
            $objectStash[] = $key;
        }

        //Create in beacon
        foreach ($beacons as $key => $item) {
            if (!empty($item['objects']) && is_array($item['objects'])) {
                foreach ($item['objects'] as $objectID) {
                    $baconStash[] = $objectID;
                }
            }
        }

        //Calculate drifference (ie. orphaned objects)
        $result = array_values(array_unique(array_diff($objectStash, $baconStash)));

        //Not present, return null.
        if (!$result) {
            return null;
        }

        //Return resutl
        return $result;
    }

    /**
     * Create array of posts object
     * @return  array
     * @version 0.3.28 Guides
     */

    public function postObjects($object, $field_name, $request, $formatted = true)
    {
        $objects = [];
        $i = 0;
        $beacons = $this->objectGetCallBack($object, 'guide_beacon', $request, true);

        foreach ($this->getObjects($object, 'guide_content_objects', $request, true) as $key => $item) {

            //Get beacon id
            $beacon_id = null;
            $beacon_distance = null;
            if ($beacons) {
                foreach ($beacons as $beacon) {
                    if (is_string($beacon['objects'])) {
                        $beacon['objects'] = explode("||", $beacon['objects']);
                    }

                    if (in_array($key, $beacon['objects'])) {
                        $beacon_id = $beacon['beacon'];
                        $beacon_distance = $beacon['distance'];
                        break;
                    }
                }
            }

            // Strip tags and and add correct amount of new lines
            $descriptionPlain = null;
            if (!empty($item['guide_object_description'])) {
                $descriptionPlain = str_replace(array("</p>"), "\n", $item['guide_object_description']);
                $descriptionPlain = strip_tags(html_entity_decode($descriptionPlain));
            }

            $objects[$key] = array(
                'order' => $i,
                'active' => ($item['guide_object_active'] == 1) ? true : false,
                'id' => empty($item['guide_object_id']) ? null : $item['guide_object_id'],
                'title' => empty($item['guide_object_title']) ? null : $item['guide_object_title'],
                'description' => empty($item['guide_object_description']) ? null : $item['guide_object_description'],
                'description_plain' => $descriptionPlain,
                'image' => !is_array($item['guide_object_image']) ? null : $this->sanitizeMediaObject($item['guide_object_image']),
                'audio' => !is_array($item['guide_object_audio']) ? null : $this->sanitizeMediaObject($item['guide_object_audio']),
                'video' => !is_array($item['guide_object_video']) ? null : $this->sanitizeMediaObject($item['guide_object_video']),
                'links' => !is_array($item['guide_object_links']) ? null : $this->sanitizeLinkObject($item['guide_object_links']),
                'bid'   => $beacon_id,
                'beacon_distance' => $beacon_distance
            );
            $i++;
        }

        return (array) $objects;
    }

    /**
     * Get post media attrubute
     * @return  array/null
     * @version 0.3.28 Guides
     */

    public function postMedia($object, $field_name, $request, $formatted = true)
    {
        $media =    $this->sanitinzeMediaObjectArray(
                        $this->objectGetCallBack($object, 'guide_main_media', $request, true)
                    );

        if (empty(array_filter($media))) {
            return null;
        } else {
            return $media;
        }
    }

    /**
     * Get post main description
     * @return  array/null
     * @version 0.3.28 Guides
     */

    public function addDescription($object, $field_name, $request, $formatted = true)
    {
        return array(
            'rendered' => $this->stringGetCallBack($object, 'guide_description', $request, $formatted),
            'plain_text' => $this->stringGetCallBack($object, 'guide_description', $request, false)
        );
    }


    /**
     * Get objects of this guide
     * @return  array
     * @version 0.3.28 Guides
     */

    public function getObjects($object, $field_name, $request, $formatted = true)
    {

        //Create cache hash
        $hash = base_convert(md5(json_encode($object)), 10, 36);

        //Look for cache (not persistent)
        if (isset($this->objectCache[$hash]) && !empty($this->objectCache[$hash])) {
            return $this->objectCache[$hash];
        }

        //Nope, get new content
        $objects = [];
        foreach ((array) $this->objectGetCallBack($object, 'guide_content_objects', $request, true) as $key => $item) {
            $objects[$item['guide_object_uid']] = $item;
        }

        //Return and save to cache (not persistent)
        return $this->objectCache[$hash] = $objects;
    }

    /**
     * Sanitize media object
     * @return  array
     * @version 0.3.28 Guides
     */

    public function sanitizeMediaObject($item)
    {
        if (is_array($item)) {
            unset($item['filename']);
            unset($item['author']);
            unset($item['caption']);
            unset($item['icon']);
        }

        return $item;
    }

    /**
     * Sanitize array of media objects
     * @return  array
     * @version 0.3.28 Guides
     */
    public function sanitinzeMediaObjectArray($objectArray)
    {
        if (!is_array($objectArray)) {
            return array();
        }

        if (array_values($objectArray) !== $objectArray) {
            return $objectArray;
        }

        foreach ((array) $objectArray as $key => $item) {
            $objectArray[$key] = $this->sanitizeMediaObject($item);
        }

        return $objectArray;
    }

    /**
     * Sanitize & validate items in link array
     * @return  array
     * @version 0.3.28 Guides
     */

    public function sanitizeLinkObject($linkObject)
    {
        $sanitizedLinkObject = [];

        foreach ((array) $linkObject as $key => $item) {
            if (!filter_var($item['guide_object_link_url'], FILTER_VALIDATE_URL) === false) {
                $sanitizedLinkObject[$key] = array(
                    'title' => $item['guide_object_link_title'],
                    'link' => $item['guide_object_link_url']
                );
            }
        }

        return $sanitizedLinkObject;
    }
}
