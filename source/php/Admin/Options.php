<?php

namespace HbgEventImporter\Admin;

class Options
{
    public function __construct()
    {
        if (function_exists('acf_add_options_sub_page')) {
            acf_add_options_sub_page(array(
                'page_title'    => _x('Event manager options', 'ACF', 'event-manager'),
                'menu_title'    => _x('Options', 'Event manager options', 'event-manager'),
                'menu_slug'     => 'acf-options-options',
                'parent_slug'   => 'edit.php?post_type=event',
                'capability'    => 'edit_users'
            ));
        }

        add_action('add_meta_boxes', array($this, 'registerMetaBoxes'));
        add_action('save_post', array($this, 'saveSyncMeta'));

        $postTypes = array('event', 'location', 'organizer');
        foreach ($postTypes as $val) {
            add_filter('get_user_option_meta-box-order_' . $val, array($this, 'metaboxOrder'));
        }
    }

    /**
     * Save sync option. Remove import client and save to new meta field
     * @param  int $post_id current post id
     * @return void
     */
    public function saveSyncMeta($post_id)
    {
        if (isset($_POST['post_title']) && get_post_meta($post_id, 'imported_post', true)) {
            $data = isset($_POST['sync-checkbox']) ? 1 : 0;
            update_post_meta($post_id, 'sync', $data);

            if ($data == 0) {
                $importClient = get_post_meta($post_id, 'import_client', true);
                add_post_meta($post_id, 'orig_import_client', $importClient, true);
                delete_post_meta($post_id, 'import_client');
            } else {
                $orig_client = get_post_meta($post_id, 'orig_import_client', true);
                add_post_meta($post_id, 'import_client', $orig_client, true);
                delete_post_meta($post_id, 'orig_import_client');
            }
        }
    }

    /**
     * Register custom meta boxes
     * @return void
     */
    public function registerMetaBoxes()
    {
        global $post;

        // Add sync meta box
        if (is_object($post) && get_post_meta($post->ID, 'imported_post', true)) {
            add_meta_box('sync-meta-box', esc_html__('API synchronization', 'event-manager'), array($this, 'syncMetaBoxCallback'), array('event', 'location', 'organizer'), 'side', 'default');
        }

        // Add meta box linked events
        if (current_user_can('administrator')) {
            add_meta_box('linked-events-box', esc_html__('Linked events', 'event-manager'), array($this, 'linkedEventsCallback'), array('location', 'organizer', 'membership-card', 'sponsor'), 'side', 'low');
        }
    }

    /**
     * Return linked events
     * @param  obj $post current post object
     * @return string
     */
    public function linkedEventsCallback($post)
    {
        $markup = '';

        if (is_object($post)) {
            switch ($post->post_type) {
                case 'organizer':
                    add_filter('posts_where', array($this, 'allowKeyWildcards'));
                    $value = 'organizers_%';
                    break;
                case 'sponsor':
                    $value = 'supporters';
                    break;
                case 'membership-card':
                    $value = 'membership_cards';
                    break;
                default:
                    $value = $post->post_type;
            }

            $args = array(
                'numberposts' => -1,
                'post_type'   => 'event',
                'post_status' => 'any',
                'meta_query'  => array(
                    array(
                        'key'     => $value,
                        'value'   => $post->ID,
                        'compare' => 'LIKE',
                    )
                )
            );
            $query = new \WP_Query($args);

            if ($query && ! empty($query->posts)) {
                foreach ($query->posts as $post) {
                    $markup .= '<p><a href="'. get_edit_post_link($post->ID) .'">' . $post->post_title . '</a></p>';
                }
            }

            wp_reset_postdata();
        }

        $markup = (! empty($markup)) ? $markup : '<p>' . __('This post is not linked to any events.', 'event-manager') . '</p>';

        echo $markup;
    }

    /**
     * Allow wild cards when quering organizers
     * @param  string $where Default where statement
     * @return string
     */
    public function allowKeyWildcards($where)
    {
        if (get_post_type() == 'organizer' && is_admin()) {
            $where = str_replace("meta_key = 'organizers_%", "meta_key LIKE 'organizers_%", $where);
        }

        return $where;
    }

    /**
     * Callback with markup for sync meta box
     * @param  obj $post current post object
     * @return void
     */
    public function syncMetaBoxCallback($post)
    {
        $value   = get_post_meta($post->ID, 'sync', true);
        $checked = $value == 1 ? 'checked="checked"' : '';
        $markup  = '<p>'. __('Unchecking this box will disable API updates sync for this particular event. Only fields with sync icon is affected by this option.', 'event-manager') .'</p>';
        $markup .= '<label><input type="checkbox" id="sync-checkbox" name="sync-checkbox" value="' . $value . '" '. $checked .'> '. __('Sync event information with API', 'event-manager') .'</label>';
        echo $markup;
    }

    /**
     * Set meta box order
     * @return array
     */
    public function metaboxOrder()
    {
        return array(
            'side' => join(
                ",",
                array(
                    'submitdiv',
                    'sync-meta-box',
            )));
    }
}
