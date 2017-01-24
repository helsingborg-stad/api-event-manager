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
                'description'          => 'Locations of events.',
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
        add_filter('views_edit-location', array($this, 'addImportButtons'));
        add_action('acf/save_post', array($this, 'updateAddressData'), 20);
        add_action('publish_location', array($this, 'setAcceptedOnPublish'), 10, 2);
        add_filter('acf/load_value/name=geo_map', array($this, 'setMapValues'), 10, 3);
        add_filter('acf/update_value/name=geo_map', array($this, 'acfUpdateMap'), 10, 3);
    }

    /**
     * Set default map data from post meta
     * @param  array  $value   the value of the field as found in the database
     * @param  int    $post_id the post id which the value was loaded from
     * @param  array  $field   the field object
     * @return array           updated $value
     */
    public function setMapValues($value, $post_id, $field)
    {
        $address = get_post_meta($post_id, 'formatted_address', true);
        $lat = get_post_meta($post_id, 'latitude', true);
        $lng = get_post_meta($post_id, 'longitude', true);

        $value['address'] = (! empty($address)) ? $address : null;
        if (! empty($lat) && ! empty($lng)) {
            $value['lat'] = $lat;
            $value['lng'] = $lng;
        }
        return $value;
    }

    /**
     * Set Google Map values to empty, this map is for read only.
     * @param  string $value   the value of the field
     * @param  int    $post_id the post id to save against
     * @param  array  $field   the field object
     * @return string          the new value
     */
    public function acfUpdateMap($value, $post_id, $field)
    {
        $value = '';
        return $value;
    }

    /**
     * Add buttons to start parsing xcap and Cbis
     * @return void
     */
    public function addImportButtons($views)
    {
        if (current_user_can('administrator')) {
            $button  = '<div class="import-buttons actions">';
            if (have_rows('cbis_api_keys', 'option')) {
                $button .= '<div class="button-primary extraspace" id="cbislocation">' . __('Import CBIS locations', 'event-manager') . '</div>';
            }
            $button .= '</div>';
            $views['import-buttons'] = $button;
        }
        return $views;
    }

    /**
     * Get missing address components when saving location
     * @param  int $post_id post id
     */
    public function updateAddressData($post_id)
    {
        if (get_post_type($post_id) != $this->slug) {
            return;
        }

        $defaultLocation = get_option('options_default_city');
        $defaultLocation = (!isset($defaultLocation) || empty($defaultLocation)) ? null : $defaultLocation;

        // Get formatted address
        $formatted = '';
        if (! empty(get_field('street_address'))) {
            $formatted = get_field('street_address') . ', ';
        } elseif (! empty(get_the_title($post_id))) {
            $formatted = get_the_title($post_id) . ', ';
        }
        $formatted .= ! empty(get_field('postal_code')) ? get_field('postal_code') . ', ' : '';
        if (! empty(get_field('city'))) {
            $formatted .= get_field('city') . ', ';
        } elseif (! empty($defaultLocation)) {
            $formatted .= $defaultLocation . ', ';
        }
        $formatted .=  ! empty(get_field('country')) ? get_field('country') : '';
        $formatted = rtrim($formatted, ', ');

        // If address and postal code is missing, search with Places API
        if (empty(get_field('street_address')) && empty(get_field('postal_code'))) {
            $address = Address::gmapsGetAddressComponents($formatted, false);
            if ($address == false) {
                update_field('latitude', '');
                update_field('longitude', '');
                return;
            }
            update_field('street_address', $address->street);
            update_field('city', $address->city);
            update_field('postal_code', $address->postalcode);
            update_field('country', $address->country);
            update_field('formatted_address', $address->formatted_address);
            update_field('latitude', $address->latitude);
            update_field('longitude', $address->longitude);
        } else {
            // Get coordinates from address
            update_field('formatted_address', $formatted);
            $address = Address::gmapsGetAddressComponents($formatted, true);
            if ($address == false) {
                $address = Address::gmapsGetAddressComponents($formatted, false);
            }

            if ($address == false) {
                update_field('latitude', '');
                update_field('longitude', '');
                return;
            }
            update_field('latitude', $address->latitude);
            update_field('longitude', $address->longitude);
        }
    }
}
