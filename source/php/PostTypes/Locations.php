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
                'hierarchical'         => true,
                'exclude_from_search'  => false,
                'taxonomies'           => array('location_categories'),
                'supports'             => array('title', 'revisions', 'editor', 'thumbnail', 'page-attributes'),
                'map_meta_cap'         => true,
                'capability_type'      => 'location',
            )
        );

        //Archive table
        $this->addTableColumn('cb', '<input type="checkbox">');
        $this->addTableColumn('title', __('Title', 'event-manager'));
        $this->addTableColumn('name', __('Address', 'event-manager'), true, function ($column, $postId) {
            echo get_post_meta($postId, 'formatted_address', true) ? get_post_meta($postId, 'formatted_address', true) : '';
        });

        $this->addTableColumn('coordinates', __('Coordinates', 'event-manager'), true, function ($column, $postId) {
            if (get_field('manual_coordinates', $postId)) {
                $lat =  str_replace(",", ".", get_field('manual_latitude', $postId));
                $lng =  str_replace(",", ".", get_field('manual_longitude', $postId));
            } else {
                $lat = get_post_meta($postId, 'latitude', true);
                $lng = get_post_meta($postId, 'longitude', true);
            }
            echo (!empty($lat) && !empty($lng)) ? $lat . ', ' . $lng : '';
        });

        $this->addTableColumn('import_client', __('Import client', 'event-manager'), true, function ($column, $postId) {
            $eventId = get_post_meta($postId, 'import_client', true);

            if (!isset($eventId[0])) {
                return;
            }

            echo get_post_meta($postId, 'import_client', true);
        });
        $this->addTableColumn('date', __('Date', 'event-manager'));

        //Filters
        add_filter('views_edit-' . $this->slug, array($this, 'addImportButtons'));
        add_action('acf/save_post', array($this, 'updateAddressData'), 20);
        add_filter('acf/load_value/name=geo_map', array($this, 'setMapValues'), 10, 3);
        add_filter('acf/update_value/name=geo_map', array($this, 'acfUpdateMap'), 10, 3);
        add_filter('manage_edit-' . $this->slug . '_columns', array($this, 'addAcceptDenyTable'));
        add_action('manage_' . $this->slug . '_posts_custom_column', array($this, 'addAcceptDenyButtons'), 10, 2);
        add_filter('admin_body_class', array($this, 'addBodyClass'));
        add_filter('enter_title_here', array($this, 'replacePlaceholder'));

        add_filter('page_attributes_dropdown_pages_args', array($this, 'limitPostTypeHierarchy'));
        add_filter('quick_edit_dropdown_pages_args', array($this, 'limitPostTypeHierarchy'));
    }

    /**
     * Limit heiracy to two levels.
     * @param  array  $args    the value of the field by definition
     * @return array           updated $args
     */
    public function limitPostTypeHierarchy($args)
    {
        global $post_type_object, $wpdb, $post;

        if ($post_type_object->name == 'location' && is_array($args)) {
            //Return if post is parent
            if (count(get_children($post->ID)) > 0) {
                return;
            }

            //Limit depth to one level
            $args['depth'] = 1;

            // Remove imported stuff
            $prohibited = $wpdb->get_results("SELECT post_id FROM {$wpdb->postmeta} WHERE meta_key = 'import_client'");

            if (is_array($prohibited)) {
                $args['exclude'] = implode(
                    ",",
                    array_map(
                        function ($item) {
                            return $item->post_id;
                        },
                        $prohibited
                    )
                );
            }
        }

        return $args;
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
        if (get_field('manual_coordinates', $post_id)) {
            $lat =  str_replace(",", ".", get_field('manual_latitude', $post_id));
            $lng =  str_replace(",", ".", get_field('manual_longitude', $post_id));
        } else {
            $lat = get_post_meta($post_id, 'latitude', true);
            $lng = get_post_meta($post_id, 'longitude', true);
        }

        if (!is_array($value)) {
            $value = array();
        }

        $value['address'] = (! empty($address)) ? $address : null;

        if (!empty($lat) && !empty($lng)) {
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
            if (have_rows('arcgis_api_urls', 'option')) {
                $button .= '<button class="button-primary extraspace single-import" data-client="arcgis">' . __('Import ArcGIS locations', 'event-manager') . '</button>';
            }

            $button .= '</div>';
            $views['import-buttons'] = $button;
        }

        return $views;
    }

    /**
     * Add new body classes: 'child-location' & 'imported'
     * @param string $classes body classes
     */
    public function addBodyClass($classes)
    {
        global $post;
        $screen = get_current_screen();
        $parent = ($screen->base == 'post' && $post->post_parent > 0) ? get_the_title($post->post_parent) : null;
        if ($parent) {
            $classes .= ' child-location ';
        }

        if ($screen->base == 'post' && get_post_meta($post->ID, 'imported_post', true) == true) {
            $classes .= ' imported ';
        }

        return $classes;
    }

    /**
     * Change title placeholder on child locations
     * @param  string   $title  placeholder string
     * @return string           new string
     */
    public function replacePlaceholder($title)
    {
        global $post;
        $parent = ($post->post_parent > 0) ? get_the_title($post->post_parent) : null;
        $screen = get_current_screen();

        if ($screen->post_type == $this->slug && $parent) {
          $title = $parent . ':';
        }

        return $title;
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
        if (!empty(get_field('street_address'))) {
            $formatted = get_field('street_address') . ', ';
        } elseif (! empty(get_the_title($post_id))) {
            $formatted = get_the_title($post_id) . ', ';
        }

        $formatted .= ! empty(get_field('postal_code')) ? get_field('postal_code') . ', ' : '';

        if (!empty(get_field('city'))) {
            $formatted .= get_field('city') . ', ';
        } elseif (! empty($defaultLocation)) {
            $formatted .= $defaultLocation . ', ';
        }

        $formatted .= !empty(get_field('country')) ? get_field('country') : '';
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
