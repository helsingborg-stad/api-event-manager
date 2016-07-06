<?php

namespace HbgEventImporter\Api;

/**
 * Adding linked post types to endpoints.
 */

class Linking
{
    public function __construct()
    {
        add_filter('rest_prepare_event', array($this, 'addEventContacts'), 10, 3);
        add_filter('rest_prepare_event', array($this, 'addEventGallery'), 15, 3);
        add_filter('rest_prepare_event', array($this, 'addEventLocation'), 20, 3);
    }

    /**
     * Register link to contact cpt, embeddable
     * @return  object
     * @version 0.3.2
     */
    public function addEventContacts($response, $post, $request)
    {
        $contact = get_post_meta($post->ID, 'contacts', true);

        if (is_array($contact) && !empty($contact)) {
            foreach ($contact as $item) {
                $response->add_link('contact', rest_url('/wp/v2/contact/' . $item), array( 'embeddable' => true ));
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

        if (is_array($location) && !empty($location)) {
            foreach ($location as $item) {
                $response->add_link('location', rest_url('/wp/v2/location/' . $item), array( 'embeddable' => true ));
            }
        }

        return $response;
    }
}
