<?php

namespace HbgEventImporter\PostTypes;

class Guides extends \HbgEventImporter\Entity\CustomPostType
{
    public function __construct()
    {
        parent::__construct(
            __('Guides', 'event-manager'),
            __('Guide', 'event-manager'),
            'guide',
            array(
                'description'          => 'Guided tours with beacon information',
                'menu_icon'            => 'data:image/svg+xml;base64,PD94bWwgdmVyc2lvbj0iMS4wIiBlbmNvZGluZz0iaXNvLTg4NTktMSI/PjxzdmcgeG1sbnM9Imh0dHA6Ly93d3cudzMub3JnLzIwMDAvc3ZnIiB4bWxuczp4bGluaz0iaHR0cDovL3d3dy53My5vcmcvMTk5OS94bGluayIgdmVyc2lvbj0iMS4xIiBpZD0iQ2FwYV8xIiB4PSIwcHgiIHk9IjBweCIgdmlld0JveD0iMCAwIDQ3OC4yOTcgNDc4LjI5NyIgc3R5bGU9ImVuYWJsZS1iYWNrZ3JvdW5kOm5ldyAwIDAgNDc4LjI5NyA0NzguMjk3OyIgeG1sOnNwYWNlPSJwcmVzZXJ2ZSIgd2lkdGg9IjUxMnB4IiBoZWlnaHQ9IjUxMnB4Ij48Zz48Zz48cGF0aCBkPSJNNDI1LjI5OCwyOC45M2wtMTIxLjU1LDc4LjJMMTgxLjkxNSwzLjk5NmMtNS45NS00LjgxNy0xNC4xNjctNS4zODMtMjAuNjgzLTEuMTMzTDM0LjAxNSw4OS44NDYgICAgYy00LjUzMywzLjExNy03LjM2Nyw4LjUtNy4zNjcsMTQuMTY3VjQxOS45M2MwLDYuMjMzLDMuNCwxMi4xODMsOS4wNjcsMTUuMDE3czEyLjE4MywyLjU1LDE3LjU2Ny0xLjEzM2wxMDcuMzgzLTczLjM4MyAgICBsMTMwLjA1LDExMy42MTdjMy4xMTcsMi44MzMsNy4wODMsNC4yNSwxMS4wNSw0LjI1YzMuMTE3LDAsNS45NS0wLjg1LDguNzgzLTIuNTVsMTMyLjg4My03OS42MTdjNS4xLTMuMTE3LDguMjE3LTguNSw4LjIxNy0xNC40NSAgICBWNDMuMDk2YzAtNi4yMzMtMy40LTExLjktOC43ODMtMTUuMDE3QzQzNy4xOTgsMjUuMjQ2LDQzMC42ODIsMjUuNTMsNDI1LjI5OCwyOC45M3ogTTQxNy42NDgsMzcyLjMzbC0xMTMuOSw2OC4yODMgICAgbC0xMjQuMS0xMDguNTE3di02NC4wMzNjMC05LjM1LTcuNjUtMTctMTctMTdjLTkuMzUsMC0xNyw3LjY1LTE3LDE3djYxLjc2N2wtODUsNTguMDgzVjExMy4wOGwxMDkuMDgzLTc0LjUxN2wxMTQuNzUsOTYuNjE3ICAgIHYyMTUuMDVjMCw5LjM1LDcuNjUsMTcsMTcsMTdzMTctNy42NSwxNy0xN1YxMzguMjk2bDk5LjE2Ny02NC4wMzNWMzcyLjMzeiIgZmlsbD0iI0ZGRkZGRiIvPjxwYXRoIGQ9Ik0yMjEuODY1LDExMS42NjNjLTYuNTE3LTYuNTE3LTE3LjI4My02LjUxNy0yNC4wODMsMGwtMjguOSwyOC45bC0yOC45LTI4LjljLTYuNTE3LTYuNTE3LTE3LjI4My02LjUxNy0yNC4wODMsMCAgICBjLTYuNTE3LDYuNTE3LTYuNTE3LDE3LjI4MywwLDI0LjA4M2wyOC45LDI4LjlsLTI4LjksMjkuMTgzYy02LjUxNyw2LjUxNy02LjUxNywxNy4yODMsMCwyNC4wODNjMy40LDMuNCw3LjY1LDUuMSwxMS45LDUuMSAgICBjNC4yNSwwLDguNzgzLTEuNywxMS45LTUuMWwyOS4xODMtMjkuMTgzbDI4LjksMjguOWMzLjQsMy40LDcuNjUsNS4xLDExLjksNS4xczguNzgzLTEuNywxMS45LTUuMSAgICBjNi41MTctNi41MTcsNi41MTctMTcuMjgzLDAtMjQuMDgzbC0yOC42MTctMjguOWwyOC45LTI4LjlDMjI4LjY2NSwxMjguOTQ2LDIyOC42NjUsMTE4LjE4LDIyMS44NjUsMTExLjY2M3oiIGZpbGw9IiNGRkZGRkYiLz48L2c+PC9nPjxnPjwvZz48Zz48L2c+PGc+PC9nPjxnPjwvZz48Zz48L2c+PGc+PC9nPjxnPjwvZz48Zz48L2c+PGc+PC9nPjxnPjwvZz48Zz48L2c+PGc+PC9nPjxnPjwvZz48Zz48L2c+PGc+PC9nPjwvc3ZnPg==',
                'public'               => true,
                'publicly_queriable'   => true,
                'show_ui'              => true,
                'show_in_nav_menus'    => true,
                'has_archive'          => true,
                'rewrite'              => array(
                    'slug'       => 'guide',
                    'with_front' => false
                ),
                'hierarchical'         => false,
                'exclude_from_search'  => true,
                'supports'             => array('title', 'revisions')
            )
        );

        $this->addTableColumn('cb', '<input type="checkbox">');
        $this->addTableColumn('title', __('Title', 'event-manager'));

        $this->addTableColumn('events', __('Includes', 'event-manager'), true, function ($column, $postId) {
            $events = get_post_meta($postId, 'events_included', true);
            if ($events) {
                $end = end($events);
                foreach ((array) $events as $key => $value) {
                    echo '<a href="'.get_edit_post_link($value).'"> '.get_the_title($value). '</a>';
                    if ($value != $end) {
                        echo ", ";
                    }
                }
            }
        }
        );

        $this->addTableColumn('date', __('Date', 'event-manager'));

        add_filter('manage_edit-' . $this->slug . '_columns', array($this, 'addAcceptDenyTable'));
        add_action('manage_' . $this->slug . '_posts_custom_column', array($this,'addAcceptDenyButtons'), 10, 2);
    }
}
