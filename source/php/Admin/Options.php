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

        add_action('add_meta_boxes', array($this, 'registerSyncBox'));
        add_action('save_post', array($this, 'saveSyncMeta'));

        $postTypes = array('event', 'location', 'contact');
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
     * When unchecking sync option, remove import client and save to new meta field
     * @param  string $value   the value of the field
     * @param  int    $post_id the post id to save against
     * @param  array  $field   the field object
     * @return string          the new value
     */
    public function acfUpdateSync($value, $post_id, $field)
    {
        if (!$value) {
            $importClient = get_post_meta($post_id, 'import_client', true);
            add_post_meta($post_id, 'orig_import_client', $importClient, true);
            delete_post_meta($post_id, 'import_client');
        } else {
            $orig_client = get_post_meta($post_id, 'orig_import_client', true);
            add_post_meta($post_id, 'import_client', $orig_client, true);
            delete_post_meta($post_id, 'orig_import_client');
        }

        return $value;
    }

    /**
     * Register api sync meta box
     * @return void
     */
    public function registerSyncBox()
    {
        global $post;
        if (get_post_meta($post->ID, 'imported_post', true)) {
            add_meta_box('sync-meta-box', esc_html__('API synchronization', 'event-manager'), array($this, 'syncMetaBoxCallback'), array('event', 'location', 'contact'), 'side', 'default');
        }
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
    public function metaboxOrder() {
        return array(
            'side' => join(
                ",",
                array(
                    'submitdiv',
                    'sync-meta-box',
            )));
    }
}
