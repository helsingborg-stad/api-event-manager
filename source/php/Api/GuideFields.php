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
        add_filter('rest_guide_query', array($this, 'addBeaconFilter'), 10, 2);
    }

     /**
     * Filter by beacons
     * @param  array           $args    The query arguments.
     * @param  WP_REST_Request $request Full details about the request.
     * @return array $args.
     **/
    public function addBeaconFilter($args, $request)
    {
        if (isset($_GET['beacon'])) {
            $nid = isset($_GET['beacon']['nid']) ? $_GET['beacon']['nid'] : null;
            $bid = isset($_GET['beacon']['bid']) ? $_GET['beacon']['bid'] : null; //Not used.

            if (!is_null($nid)) {
                $result = get_posts(array(
                    'post_type'     => 'guide',
                    'post_status'   => 'publish',
                    'meta_key'      => 'guide_beacon_namespace',
                    'meta_value'    => sanitize_text_field($nid)
                ));

                if (!empty($result)) {
                    if (!is_array($args['post__in'])) {
                        $args['post__in'] = [];
                    }

                    foreach ($result as $item) {
                        $args['post__in'][] = $item->ID;
                    }
                } else {
                    $args['post__in'][] = 0;
                }
            }
        }

        return $args;
    }

    /**
     * Register rest fields to consumer api
     * @return  void
     * @version 0.3.28 creating consumer accessable meta values.
     */

    public static function registerTaxonomyRestFields()
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
    public static function registerRestFields()
    {

        // Title as plain text
        register_rest_field($this->postType,
            'title',
            array(
                'get_callback'    => array($this, 'addPlaintextField'),
                'update_callback' => null,
                'schema'          => null,
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

        register_rest_field($this->postType,
            'guideBeacon',
            array(
                'get_callback' => array($this, 'postBeacon'),
                'schema' => array(
                    'description' => 'Objects of this guide.',
                    'type' => 'object',
                    'context' => array('view', 'embed')
                )
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
            'contentObjects',
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
            'subAttractions',
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
            'orphanContentObjects',
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
        $beacon_namespace   = $this->stringGetCallBack($object, 'guide_beacon_namespace', $request, $formatted);
        if (! $beacons) {
            return null;
        }
        foreach ($beacons as $key => $item) {
            if (!empty($item['objects'])) {

                if (is_string($item['objects'])) {
                    $item['objects'] = explode("||", $item['objects']);
                }

                $result[] = array(
                    'order' => $key,
                    'nid' => $beacon_namespace,
                    'bid' => $item['beacon'],
                    'content' => $item['objects'],
                    'location' => is_numeric($item['location']) ? $item['location'] : null
                );
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
     * Create array with guide response data
     * @return  array
     * @version 0.3.28 Guides
     */

    public function postBeacon($object, $field_name, $request, $formatted = true)
    {
        $beacon = array(
            'nid' => $this->stringGetCallBack($object, 'guide_beacon_namespace', $request, $formatted)
        );

        if (empty(array_filter($beacon))) {
            return null;
        } else {
            return $beacon;
        }
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
            if ($beacons) {
                foreach ($beacons as $beacon) {
                    if (is_string($beacon['objects'])) {
                        $beacon['objects'] = explode("||", $beacon['objects']);
                    }

                    if (in_array($key, $beacon['objects'])) {
                        $beacon_id = $beacon['beacon'];
                        break;
                    }
                }
            }

            $objects[$key] = array(
                'order' => $i,
                'active' => ($item['guide_object_active'] == 1) ? true : false,
                'id' => empty($item['guide_object_id']) ? null : $item['guide_object_id'],
                'title' => empty($item['guide_object_title']) ? null : $item['guide_object_title'],
                'description' => empty($item['guide_object_description']) ? null : $item['guide_object_description'],
                'description_plain' => empty($item['guide_object_description']) ? null : strip_tags(html_entity_decode($item['guide_object_description'])),
                'image' => !is_array($item['guide_object_image']) ? null : $this->sanitizeMediaObject($item['guide_object_image']),
                'audio' => !is_array($item['guide_object_audio']) ? null : $this->sanitizeMediaObject($item['guide_object_audio']),
                'video' => !is_array($item['guide_object_video']) ? null : $this->sanitizeMediaObject($item['guide_object_video']),
                'links' => !is_array($item['guide_object_links']) ? null : $this->sanitizeLinkObject($item['guide_object_links']),
                'bid'   => $beacon_id,
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
