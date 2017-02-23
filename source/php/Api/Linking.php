<?php

namespace HbgEventImporter\Api;

/**
 * Adding linked post types to endpoints.
 */

class Linking extends Fields
{
    private $addedHAL = [];

    public function __construct()
    {
        add_filter('rest_prepare_event', array($this, 'addEventContacts'), 10, 3);
        add_filter('rest_prepare_event', array($this, 'addEventGallery'), 15, 3);
        add_filter('rest_prepare_event', array($this, 'addEventLocation'), 20, 3);
        add_filter('rest_prepare_event', array($this, 'addEventAddLocations'), 20, 3);
        add_filter('rest_prepare_event', array($this, 'addEventSponsors'), 20, 3);
        add_filter('rest_prepare_event', array($this, 'addEventRelatedEvents'), 20, 3);
        add_filter('rest_prepare_event', array($this, 'addEventMemberCards'), 20, 3);
        add_filter('rest_prepare_event', array($this, 'addEmbedLink'), 20, 3);

        add_filter('rest_prepare_location', array($this, 'addEventGallery'), 15, 3);
        add_filter('rest_prepare_location', array($this, 'addEmbedLink'), 20, 3);

        add_filter('rest_prepare_package', array($this, 'addEventMemberCards'), 20, 3);
        add_filter('rest_prepare_package', array($this, 'addIncludedEvents'), 20, 3);
        add_filter('rest_prepare_package', array($this, 'addEmbedLink'), 20, 3);

        add_filter('rest_prepare_guidegroup', array($this, 'addGuideLocation'), 20, 3);
        add_filter('rest_prepare_guide', array($this, 'addGuideSubLocation'), 20, 3);
        add_filter('rest_prepare_guide', array($this, 'addEmbedLink'), 20, 3);
    }

    /**
     * Register link to contacts, embeddable
     * @return  object
     */
    public function addEventContacts($response, $post, $request)
    {
        $repeater  = 'organizers';
        $count = intval(get_post_meta($post->ID, $repeater, true));
        for ($i=0; $i<$count; $i++) {
            $getField   = $repeater.'_'.$i.'_'.'contacts';
            $contacts   = get_post_meta($post->ID, $getField, true);
            if (is_array($contacts) && !empty($contacts)) {
                foreach ($contacts as $item) {
                    $response->add_link('contacts', rest_url('/wp/v2/contact/' . $item), array( 'embeddable' => true ));
                }
            }
        }
        return $response;
    }

    /**
     * Register link to gallery items / media links, embeddable
     * @return  object
     * @version 0.3.2
     */
    public function addEventGallery($response, $post, $request)
    {
        $gallery = get_post_meta($post->ID, 'gallery', true);

        if (is_array($gallery) && !empty($gallery)) {
            foreach ($gallery as $item) {
                $response->add_link('gallery', rest_url('/wp/v2/media/' . $item), array( 'embeddable' => true ));
            }
        }

        return $response;
    }

    /**
     * Register link to location cpt, embeddable
     * @return  object
     * @version 0.3.2
     */
    public function addEventLocation($response, $post, $request)
    {
        $location = get_post_meta($post->ID, 'location', true);

        if ($location) {
            $response->add_link('location', rest_url('/wp/v2/location/' . $location), array( 'embeddable' => true ));
        }

        return $response;
    }

    /**
     * Register link to additional locations, embeddable
     * @return  object
     */
    public function addEventAddLocations($response, $post, $request)
    {
        $additional = get_post_meta($post->ID, 'additional_locations', true);

        if (is_array($additional) && !empty($additional)) {
            foreach ($additional as $item) {
                $response->add_link('additional_locations', rest_url('/wp/v2/location/' . $item), array( 'embeddable' => true ));
            }
        }
        return $response;
    }

    /**
     * Register link to sponsors cpt, embeddable
     * @return  object
     * @version 0.3.2
     */
    public function addEventSponsors($response, $post, $request)
    {
        $sponsors = get_post_meta($post->ID, 'supporters', true);

        if (is_array($sponsors) && !empty($sponsors)) {
            foreach ($sponsors as $item) {
                $response->add_link('sponsors', rest_url('/wp/v2/sponsor/' . $item), array( 'embeddable' => true ));
            }
        }
        return $response;
    }

    /**
     * Register link to related events, embeddable
     * @return  object
     */
    public function addEventRelatedEvents($response, $post, $request)
    {
        $related = get_post_meta($post->ID, 'related_events', true);

        if (is_array($related) && !empty($related)) {
            foreach ($related as $item) {
                $response->add_link('related_events', rest_url('/wp/v2/event/' . $item), array( 'embeddable' => true ));
            }
        }
        return $response;
    }

    /**
     * Register link to related membership cards, embeddable
     * @return  object
     */
    public function addEventMemberCards($response, $post, $request)
    {
        $cards = get_post_meta($post->ID, 'membership_cards', true);

        if (is_array($cards) && !empty($cards)) {
            foreach ($cards as $item) {
                $response->add_link('membership-cards', rest_url('/wp/v2/membership-card/' . $item), array( 'embeddable' => true ));
            }
        }
        return $response;
    }

    /**
     * Register link to embedded version of object
     * @return  object
     * @version 0.3.4
     */
    public function addEmbedLink($response, $post, $request)
    {
        $response->add_link('complete', rest_url('/wp/v2/'.$post->post_type. '/' . $post->ID . '/?_embed'));
        return $response;
    }

    /**
     * Register link to additional locations, embeddable
     * @return  object
     */
    public function addIncludedEvents($response, $post, $request)
    {
        $included = get_post_meta($post->ID, 'events_included', true);

        if (is_array($included) && !empty($included)) {
            foreach ($included as $item) {
                $response->add_link('events_included', rest_url('/wp/v2/event/' . $item), array( 'embeddable' => true ));
            }
        }
        return $response;
    }

    /**
     * Register link to connected locations, embeddable
     * @return  object
     */
    public function addGuideLocation($response, $taxonomy, $request)
    {
        $id = get_field('guide_taxonomy_location', $taxonomy->taxonomy. '_' . $taxonomy->id);

        if (!is_null($id)) {
            if ($this->hasDuplicateHAL($post, $id)) {
                $response->add_link(
                    'location',
                    rest_url('/wp/v2/location/' . $id),
                    array( 'embeddable' => true )
                );

                $this->addedHAL[$post->ID][] = $id;
            }
        }

        return $response;
    }

    /**
     * Register link to connected sub-locations, embeddable
     * @return  object
     */
    public function addGuideSubLocation($response, $post, $request)
    {
        foreach ((array) $this->objectGetCallBack(array('id' => $post->ID), 'guide_beacon', $request, true) as $item) {
            if (isset($item['location']) && is_numeric($item['location'])) {
                if (!$this->hasDuplicateHAL($post, $item['location'])) {
                    $response->add_link(
                        'location',
                        rest_url('/wp/v2/location/' . $item['location']),
                        array( 'embeddable' => true )
                    );

                    $this->addedHAL[$post->ID][] = $item['location'];
                }
            }
        }
        return $response;
    }

    /**
     * Prevent duplicate HAL objects to be registered
     * @return  boolean
     */
    public function hasDuplicateHAL($currentObject, $linkId)
    {

        // Create object array if not extists (return if not exists)
        if (!isset($this->addedHAL[$currentObject->ID])) {
            $this->addedHAL[$currentObject->ID] = [];
            return false;
        }

        //Check for link
        if (in_array($linkId, $this->addedHAL[$currentObject->ID])) {
            return true;
        }

        return false;
    }
}
