<?php

namespace HbgEventImporter\PostTypes;

use \HbgEventImporter\Helper\Address as Address;

class Locations extends \HbgEventImporter\Entity\CustomPostType
{
    public function __construct()
    {
        parent::__construct(
            __('Locations', 'event-manager'),
            __('Location', 'event-manager'),
            'location',
            array(
                'description'          => 'Locations',
                'menu_icon'            => 'data:image/svg+xml;base64,PHN2ZyB4bWxucz0iaHR0cDovL3d3dy53My5vcmcvMjAwMC9zdmciIHdpZHRoPSI1MTIiIGhlaWdodD0iNTEyIiB2aWV3Qm94PSIwIDAgODk2IDg5NiI+PHBhdGggZD0iTTQ0OCAwQzI2Mi43MiAwIDExMiAxNTAuNzIgMTEyIDMzNmMwIDcwLjU4NyAyMS45NTggMTM4LjMwNCA2My40MjQgMTk1Ljg5bDguODE4IDEyLjM2IDI0MS4zNjMgMzQwLjU0QTI4LjAyNiAyOC4wMjYgMCAwIDAgNDQ4IDg5NmM4Ljc5IDAgMTcuMTAzLTQuMTU2IDIyLjM5NC0xMS4yMUw3MDguNzUgNTQ4LjI5NyA3MjAuNTEgNTMyQzc2Mi4wNDMgNDc0LjMwNCA3ODQgNDA2LjU4NyA3ODQgMzM2IDc4NCAxNTAuNzIgNjMzLjI4IDAgNDQ4IDB6bTAgNDQ4Yy02MS43NyAwLTExMi01MC4yMy0xMTItMTEyczUwLjIzLTExMiAxMTItMTEyIDExMiA1MC4yMyAxMTIgMTEyLTUwLjIzIDExMi0xMTIgMTEyeiIgZmlsbD0iI0ZGRiIvPjwvc3ZnPg==',
                'public'               => true,
                'publicly_queriable'   => true,
                'show_ui'              => true,
                'show_in_nav_menus'    => true,
                'has_archive'          => true,
                'rewrite'              => array(
                    'slug'       => 'location',
                    'with_front' => false
                ),
                'hierarchical'         => false,
                'exclude_from_search'  => false,
                'taxonomies'           => array('location_categories'),
                'supports'             => array('title', 'revisions', 'editor', 'thumbnail')
            )
        );
        $this->addTableColumn('cb', '<input type="checkbox">');
        $this->addTableColumn('title', __('Title', 'event-manager'));
        $this->addTableColumn('name', __('Address', 'event-manager'), true, function ($column, $postId) {
            echo get_post_meta($postId, 'formatted_address', true) ? get_post_meta($postId, 'formatted_address', true) :  __('n/a', 'event-manager');
        });

        $this->addTableColumn('coordinates', __('Coordinates', 'event-manager'), true, function ($column, $postId) {
            $lat = get_post_meta($postId, 'latitude', true);
            $lng = get_post_meta($postId, 'longitude', true);
            if (!isset($lat[0]) || !isset($lng[0])) {
                return;
            }
            echo get_post_meta($postId, 'latitude', true).', '.get_post_meta($postId, 'longitude', true);
        });
        $this->addTableColumn('import_client', __('Import client', 'event-manager'), true, function ($column, $postId) {
            $eventId = get_post_meta($postId, 'import_client', true);
            if (!isset($eventId[0])) {
                return;
            }
            echo get_post_meta($postId, 'import_client', true);
        });
        $this->addTableColumn('acceptAndDeny', __('Public', 'event-manager'), true, function ($column, $postId) {
            $metaAccepted = get_post_meta($postId, 'accepted');
            if (!isset($metaAccepted[0])) {
                add_post_meta($postId, 'accepted', 0);
                $metaAccepted[0] = 0;
            }
            $first = '';
            $second = '';
            if ($metaAccepted[0] == 1) {
                $first = 'hiddenElement';
            } elseif ($metaAccepted[0] == -1) {
                $second = 'hiddenElement';
            } elseif ($metaAccepted[0] == 0) {
                $first = 'hiddenElement';
                $second = 'hiddenElement';
                echo '<a href="'.get_edit_post_link($postId).'" title="'.__('This post needs to be edited before it can be published', 'event-manager').'" class="button" postid="' . $postId . '">' . __('Edit draft') . '</a>';
            }
            echo '<a href="#" class="accept button-primary ' . $first . '" postid="' . $postId . '">' . __('Accept', 'event-manager') . '</a>
            <a href="#" class="deny button-primary ' . $second . '" postid="' . $postId . '">' . __('Deny', 'event-manager') . '</a>';
        });
        $this->addTableColumn('date', __('Date', 'event-manager'));
        add_action('manage_posts_extra_tablenav', array($this, 'tablenavButtons'));
        add_action('acf/save_post', array($this, 'updateAddressData'), 20);
        add_action('publish_location', array($this, 'setAcceptedOnPublish'), 10, 2);
    }

    /**
     * Add buttons to start parsing locations from Cbis
     * @return void
     */
    public function tablenavButtons($which)
    {
        global $current_screen;

        if ($current_screen->id != 'edit-location' || $which != 'top') {
            return;
        }

        if (current_user_can('manage_options')) {
            echo '<div class="alignleft actions" style="position: relative;">';
            echo '<div class="button-primary extraspace" id="cbislocation">' . __('Import CBIS locations', 'event-manager') . '</div>';
            echo '</div>';
        }
    }

    /**
     * Automatically updates missing address components when saving location
     * @param  int $post_id post id
     */
    public function updateAddressData($post_id)
    {
        if (get_post_type($post_id) != $this->slug) {
            return;
        }

        $defaultLocation = get_option('options_default_city');
        $defaultLocation = (!isset($defaultLocation) || empty($defaultLocation)) ? null : $defaultLocation;
        $formatted = get_field('street_address') != null ? get_field('street_address') : '';
        $formatted .= get_field('postal_code') != null ? ', ' . get_field('postal_code') : '';
        $formatted .= get_field('city') != null ? ', ' . get_field('city') : ', ' . $defaultLocation;
        $formatted .= get_field('country') != null ? ', ' . get_field('country') : '';
        if (get_field('street_address') != null) {
            update_field('formatted_address', $formatted);
        }

        // Get coordinates from address
        if (get_field('street_address') != null && get_field('postal_code') != null && get_field('city') != null && (get_field('latitude') == null || get_field('longitude') == null)) {
            $address = Address::gmapsGetAddressComponents($formatted, true);
            if ($address) {
                update_field('latitude', $address->latitude);
                update_field('longitude', $address->longitude);
            }
        }
        // Get address from coordinates
        elseif (get_field('street_address') == null && get_field('postal_code') == null && get_field('city') == null && get_field('latitude') != null || get_field('longitude') != null) {
            $address = Address::gmapsGetAddressByCoordinates(get_field('latitude'),  get_field('longitude'));
            if ($address) {
                update_field('street_address', $address->street);
                update_field('city', $address->city);
                update_field('postal_code', $address->postalcode);
                update_field('country', $address->country);
                update_field('formatted_address', $address->formatted_address);
            }
        }
        // Get address and coordinates from post title
        elseif (get_field('street_address') == null && get_field('postal_code') == null && get_field('city') == null && (get_field('latitude') == null || get_field('longitude') == null)) {
            $title = get_the_title($post_id);
            $title .= $defaultLocation != null ? ', ' . $defaultLocation : '';
            $address = Address::gmapsGetAddressComponents($title, false);
            if ($address) {
                update_field('street_address', $address->street);
                update_field('city', $address->city);
                update_field('postal_code', $address->postalcode);
                update_field('country', $address->country);
                update_field('formatted_address', $address->formatted_address);
                update_field('latitude', $address->latitude);
                update_field('longitude', $address->longitude);
            }
        }
    }
}
