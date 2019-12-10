<?php

namespace HbgEventImporter\Entity;

abstract class PostManager
{
    /**
     * Post object sticky values
     */
    public $post_type = null;
    public $post_status = 'publish';

    /**
     * Keys that counts as post object properties
     * Any other key will be treated as meta properties
     * @var array
     */
    public $allowedPostFields = array(
        'ID',
        'post_author',
        'post_date',
        'post_date_gmt',
        'post_content',
        'post_content_filtered',
        'post_title',
        'post_excerpt',
        'post_status',
        'comment_status',
        'ping_status',
        'post_password',
        'post_name',
        'to_ping',
        'pinged',
        'post_modified',
        'post_modified_gmt',
        'post_parent',
        'menu_order',
        'post_mime_type',
        'guid'
    );

    /**
     * Constructor
     * @param array $postData Post object fields as array
     * @param array $metaData Post meta as array
     */
    public function __construct($postData = array(), $metaData = array())
    {
        if (is_null($this->post_type)) {
            throw new \Exception('You need to specify a post type by setting the class property $postType');
            exit;
        }

        // Add post data as separate object parameters
        foreach ($postData as $key => $value) {
            $this->{$key} = $value;
        }

        // Add meta data as separate object parameters
        foreach ($metaData as $key => $value) {
            $this->{$key} = $value;
        }
    }

    /**
     * Save hooks
     * @param  string $postType Saved post type
     * @param  object $object   Saved object
     * @return void
     */
    public function beforeSave()
    {

    }

    public function afterSave()
    {
        return true;
    }

    /**
     * Get  posts
     * @param  integer      $count      Number of posts to get
     * @param  array        $metaQuery  Meta query
     * @param  string       $postType   Post type
     * @param  array|string $postStatus Post status
     * @return array                       Found posts
     */
    public static function get($count, $metaQuery, $postType, $postStatus = array('publish', 'draft'))
    {
        $args = array(
            'posts_per_page' => $count,
            'post_type' => $postType,
            'orderby' => 'date',
            'order' => 'DESC'
        );

        $args['post_status'] = (array)$postStatus;

        if (is_array($metaQuery)) {
            $args['meta_query'] = $metaQuery;
        }

        $posts = get_posts($args);

        if ($count == 1 && isset($posts[0])) {
            $posts = $posts[0];
        }

        return $posts;
    }

    /**
     * Remove all values that are empty, except the value 0
     * @param  $metaValue
     * @return $metaValue
     */
    public function removeEmpty($metaValue)
    {
        if (is_array($metaValue)) {
            return $metaValue;
        }

        return $metaValue !== null && $metaValue !== false && $metaValue !== '';
    }

    /**
     * Saves the event and it's data
     * @return integer The inserted/updated post id
     */
    public function save()
    {
        $this->beforeSave();

        // Arrays for holding save data
        $post = array();
        $meta = array();
        $post['post_status'] = $this->post_status;

        // Get the default class variables and set it's keys to forbiddenKeys
        $defaultData = get_class_vars(get_class($this));
        $forbiddenKeys = array_keys($defaultData);

        $data = array_filter(get_object_vars($this), function ($item) use ($forbiddenKeys) {
            return !in_array($item, $forbiddenKeys);
        }, ARRAY_FILTER_USE_KEY);

        // If data key is allowed post field add to $post else add to $meta
        foreach ($data as $key => $value) {
            if (in_array($key, $this->allowedPostFields)) {
                $post[$key] = $value;
                continue;
            }

            $meta[$key] = $value;
        }

        // Do not include null values in meta
        $meta = array_filter($meta, array($this, 'removeEmpty'));

        $post['post_type'] = $this->post_type;
        $post['meta_input'] = $meta;

        // Skip these meta values, it will be saved later with ACF
        unset($post['meta_input']['occasions']);
        unset($post['meta_input']['additional_ticket_types']);
        unset($post['meta_input']['additional_ticket_retailers']);
        unset($post['meta_input']['links']);

        // Check if duplicate by matching "_event_manager_uid" meta value
        if (isset($meta['_event_manager_uid'])) {
            $duplicate = self::get(
                1,
                array(
                    'relation' => 'OR',
                    array(
                        'key' => '_event_manager_uid',
                        'value' => $meta['_event_manager_uid'],
                        'compare' => '='
                    )
                ),
                $this->post_type
            );
        }

        if (isset($duplicate->ID)) {
            // Check if any updates has been made
            $ifDiff = $this->postDiff($duplicate->ID, $post);
            // Update if duplicate and sync is set to true
            if (get_post_meta($duplicate->ID, 'sync', true) == true && $ifDiff == true) {
                $this->ID = $duplicate->ID;
                $post['ID'] = $duplicate->ID;
                wp_update_post($post);
            } else {
                $this->ID = $duplicate->ID;
                return false;
            }
        } else {
            // Create if not duplicate
            $this->ID = wp_insert_post($post, true);
        }

        $this->saveDataHash($this->ID, $post);
        return $this->afterSave();
    }

    /**
     * Check if the post has been updated
     * @param $postId
     * @param $data
     * @return bool
     */
    public function postDiff($postId, $data): bool
    {
        // Check if new occasions has been added to post type Event
        if (isset($data['post_type']) && $data['post_type'] == 'event' && !empty($this->occasions) ) {
            // Return true if new occasions has been added
            if ($this->hasNewOccasions((int)$postId, $this->occasions)) {
                return true;
            }
        }

        // Compare old hashed post data with the new
        $oldDataHash = get_post_meta($postId, 'data_hash', true);
        $newDataHash = md5(serialize($this->cleanBeforeHash($data)));

        return $oldDataHash != $newDataHash;
    }

    /**
     * Check if new occasions has been added
     * @param $postId
     * @param $occasions
     * @return bool
     */
    public function hasNewOccasions($postId, $occasions): bool
    {
        global $wpdb;
        $table = $wpdb->prefix . "occasions";
        foreach ($occasions as $occasion) {
            $timestampStart = strtotime($occasion['start_date']);
            $timestampEnd = strtotime($occasion['end_date']);
            $exist = $wpdb->get_var("SELECT COUNT(*) FROM {$table} WHERE event = {$postId} AND timestamp_start = {$timestampStart} AND timestamp_end = {$timestampEnd}");

            if ($exist == 0) {
                return true;
            }
        }

        return false;
    }

    /**
     * Remove some meta fields before being hashed
     * @param $data
     * @return mixed
     */
    public function cleanBeforeHash($data)
    {
        unset($data['ID']);
        $metaFields = array(
            'tickets_remaining',
            'ticket_release_date',
            'ticket_stock',
            'occurred',
            'categories'
            );
        foreach ($metaFields as $field) {
            unset($data['meta_input'][$field]);
        }

        return $data;
    }

    /**
     * Saves hashed post data to meta
     * @param $postId
     * @param $data
     */
    public function saveDataHash($postId, $data)
    {
        $data = md5(serialize($this->cleanBeforeHash($data)));
        update_post_meta($postId, 'data_hash', $data);
    }

    /**
     * Uploads an image from a specified url and sets it as the current post's featured image
     * @param $url
     * @param $featured
     * @return bool|object
     */
    public function setFeaturedImageFromUrl($url, $featured)
    {

        if (!isset($this->ID)) {
            return false;
        }

        if (!isset($url) || strlen($url) === 0 || !$this->isUrl($url)) {
            return false;
        }

        // Upload path
        $uploadDir = wp_upload_dir()['basedir'].'/events/' . date("Y"). "/" . date("m");

        //Create dir
        if (!is_dir($uploadDir)) {
            if (!mkdir($uploadDir, 0776, true)) {
                return new WP_Error('event', __('Could not create folder', 'event-integration') . ' "' . $uploadDir . '", ' . __('please go ahead and create it manually and rerun the import.', 'event-integration'));
            }
        }

        //Get slug & validate that post exists
        if(is_string(get_post_status($this->ID)) && $filename = get_post($this->ID)->post_name) {

            $mimes = new \Mimey\MimeTypes;

            //Temp name
            $filenameTemp = $filename . ".tmp"; 
            
            // Get the file content
            $options = array(
                'ssl' => array(
                    'verify_peer' => defined('DEV_MODE') && DEV_MODE === true ? false : true,
                    'verify_peer_name' => defined('DEV_MODE') && DEV_MODE === true ? false : true
                )
            );
            $imgContent = file_get_contents($url, false, stream_context_create($options));

            //File is empty abort
            if(!$imgContent) {
                return; 
            }

            // Save file to temp
            $tempStoreFile = fopen($uploadDir . '/' . $filenameTemp, 'w');
            fwrite($tempStoreFile, $imgContent);
            fclose($tempStoreFile);

            // Detect file type
            $filetype = $mimes->getExtension(
                $mimeFileType = mime_content_type($uploadDir . '/' . $filenameTemp)
            );

            // Move to real extension
            rename($uploadDir . '/' . $filenameTemp, $uploadDir . '/' . $filename . '.' . $filetype);

            // Insert the file to media library
            $attachmentId = wp_insert_attachment(
                array(
                    'guid' => $uploadDir . '/' . $filename . '.' . $filetype,
                    'post_mime_type' => $mimeFileType,
                    'post_title' => $filename,
                    'post_content' => '',
                    'post_status' => 'inherit',
                    'post_parent' => $this->ID
                ), 
                $uploadDir . '/' . $filename . "." . $filetype, 
                $this->ID
            );

            if($attachmentId) {
                update_post_meta($attachmentId, 'event-manager-media', 1); //Filter in [/Admin/MediaLibrary.php]
            }

            //Bind the image to the event
            $this->bindImageToEvent($attachmentId, $featured); 
        } 

        return true;
    }

    /**
     * Adds the image to the database as a galleryitem or featured image
     * @param $attachmentId
     * @param $featured
     * @return bool|object
     */
    public function bindImageToEvent($attachmentId, $featured = true) {
        // Set image as featured image or add to gallery
        if ($featured) {
            set_post_thumbnail($this->ID, $attachmentId);
        } else {
            $gallery_meta = get_post_meta($this->ID, 'event_gallery', true);
            if (empty($gallery_meta)) {
                add_post_meta($this->ID, 'event_gallery', array($attachmentId));
            } else {
                $gallery_meta[] .= $attachmentId;
                update_post_meta($this->ID, 'event_gallery', array_unique($gallery_meta));
            }
        }
    }

    /**
     * Checks if a attachment src already exists in media library
     * @param  string $src Media url
     * @return mixed
     */
    private function attachmentExists($src)
    {
        global $wpdb;
        $query = "SELECT ID FROM {$wpdb->posts} WHERE guid = '$src'";
        $id = $wpdb->get_var($query);

        if (!empty($id) && $id > 0) {
            return $id;
        }

        return false;
    }
}
