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
                'taxonomies'           => array('location-categories'),
                'supports'             => array('title', 'revisions', 'editor', 'thumbnail')
            )
        );
        // TA BORT
        //add_action('manage_posts_extra_tablenav', array($this, 'tablenavButtons'));
        add_action('acf/save_post', array($this, 'updateAddressData'), 20);
        $this->addTableColumn('cb', '<input type="checkbox">');
        $this->addTableColumn('title', __('Title'));
        $this->addTableColumn('name', __('Address'), true, function ($column, $postId) {
            echo get_post_meta($postId, 'formatted_address', true) ? get_post_meta($postId, 'formatted_address', true) : 'n/a';
        });

        $this->addTableColumn('coordinates', __('Coordinates'), true, function ($column, $postId) {
            $lat = get_post_meta($postId, 'latitude', true);
            $lng = get_post_meta($postId, 'longitude', true);
            if (!isset($lat[0]) || !isset($lng[0])) {
                return;
            }
            echo get_post_meta($postId, 'latitude', true).', '.get_post_meta($postId, 'longitude', true);
        });

        $this->addTableColumn('date', __('Date'));

        $this->addTableColumn('import_client', __('Import client'), true, function ($column, $postId) {
            $eventId = get_post_meta($postId, 'import_client', true);
            if (!isset($eventId[0])) {
                return;
            }
            echo get_post_meta($postId, 'import_client', true);
        });

        // TA BORT
        // $this->addTableColumn('debug_flag', __('Debug Flag'), true, function ($column, $postId) {
        //     $eventId = get_post_meta($postId, 'debug_flag', true);
        //     if (!isset($eventId[0])) {
        //         return;
        //     }
        //     echo get_post_meta($postId, 'debug_flag', true);
        // });
    }




    public function updateAddressData($post_id)
    {
        if (get_post_type($post_id) != 'location') {
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
                update_field('postal_code', str_replace(' ', '', $address->postalcode));
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
                update_field('postal_code', str_replace(' ', '', $address->postalcode));
                update_field('country', $address->country);
                update_field('formatted_address', $address->formatted_address);
                update_field('latitude', $address->latitude);
                update_field('longitude', $address->longitude);
            }
        }
    }

    /**
     * TA BORT
     * Add buttons to start parsing CBIS Locations
     * @return void
     */
    // public function tablenavButtons($which)
    // {
    //     global $current_screen;

    //     if ($current_screen->id != 'edit-location' || $which != 'top') {
    //         return;
    //     }

    //     if (current_user_can('manage_options')) {
    //         echo '<div class="alignleft actions" style="position: relative;">';
    //         echo '<a href="' . admin_url('options.php?page=import-cbis-locations') . '" class="button-primary" id="post-query-submit">Import CBIS locations</a>';
    //         echo '</div>';
    //     }
    // }
}
