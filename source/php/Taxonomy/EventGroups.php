<?php

namespace HbgEventImporter\Taxonomy;

class EventGroups
{
    public function __construct()
    {
        add_action('init', array($this, 'registerTaxonomy'));
        add_action( 'show_user_profile', array($this, 'displayUserGroups'));
        add_action( 'edit_user_profile', array($this, 'displayUserGroups'));
    }

    public function registerTaxonomy()
    {
        $labels = array(
            'name'                  => _x('Groups', 'Taxonomy plural name', 'event-manager'),
            'singular_name'         => _x('Group', 'Taxonomy singular name', 'event-manager'),
            'search_items'          => __('Search groups', 'event-manager'),
            'popular_items'         => __('Popular groups', 'event-manager'),
            'all_items'             => __('All groups', 'event-manager'),
            'parent_item'           => __('Parent group', 'event-manager'),
            'parent_item_colon'     => __('Parent group', 'event-manager'),
            'edit_item'             => __('Edit group', 'event-manager'),
            'update_item'           => __('Update group', 'event-manager'),
            'add_new_item'          => __('Add new group', 'event-manager'),
            'new_item_name'         => __('New group', 'event-manager'),
            'add_or_remove_items'   => __('Add or remove groups', 'event-manager'),
            'choose_from_most_used' => __('Choose from most used groups', 'event-manager'),
            'menu_name'             => __('Groups', 'event-manager'),
        );

        $capabilities = array(
            'manage_terms' => 'publish_pages',
            'edit_terms'   => 'publish_pages',
            'delete_terms' => 'publish_pages',
            'assign_terms' => 'read',
        );

        $args = array(
            'capabilities'          => $capabilities,
            'labels'                => $labels,
            'public'                => true,
            'show_in_nav_menus'     => true,
            'show_admin_column'     => true,
            'hierarchical'          => false,
            'show_tagcloud'         => false,
            'show_ui'               => true,
            'query_var'             => true,
            'rewrite'               => true,
            'meta_box_cb'           => false,
        );

        register_taxonomy('event_groups', array('event'), $args);
    }

    /**
     * Adds a new section on user profile with the assigned groups.
     * @param object $user The user object currently being edited.
     */
    function displayUserGroups($user) {

    // Return if admin or editor
    if (current_user_can('editor') || current_user_can('administrator')) {
        return;
    }

    $id = 'user_' . $user->ID;
    $groups = get_field('event_user_groups', $id);
    ?>

        <h2><?php _e('Event publishing groups', 'event-manager') ?></h2>
        <table class="form-table">
            <tr>
                <th><label for="groups"><?php _e( 'Assigned groups', 'event-manager'); ?></label></th>
                <td>
                <?php if (! empty($groups)) : ?>
                    <ul>
                    <?php foreach ( $groups as $group ) : ?>
                    <li>
                        <?php $term = get_term($group) ?>
                        <?php echo $term->name; ?>
                    </li>
                    <?php endforeach; ?>
                <?php else: ?>
                   <?php _e( 'There are no groups assigned to your account.', 'event-manager' ); ?>
                <?php endif; ?>
                </td>
            </tr>
        </table>
    <?php }

}
