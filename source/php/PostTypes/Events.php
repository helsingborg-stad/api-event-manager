<?php

namespace HbgEventImporter\PostTypes;

class Events extends \HbgEventImporter\Entity\CustomPostType
{
    public function __construct()
    {
        parent::__construct(
            __('Events', 'event-manager'),
            __('Event', 'event-manager'),
            'event',
            array(
                'description'          =>   'Events',
                'menu_icon'            =>   'data:image/svg+xml;base64,PD94bWwgdmVyc2lvbj0iMS4wIiBlbmNvZGluZz0iaXNvLTg4NTktMSI/Pg0KPCEtLSBHZW5lcmF0b3I6IEFkb2JlIElsbHVzdHJhdG9yIDE2LjAuMCwgU1ZHIEV4cG9ydCBQbHVnLUluIC4gU1ZHIFZlcnNpb246IDYuMDAgQnVpbGQgMCkgIC0tPg0KPCFET0NUWVBFIHN2ZyBQVUJMSUMgIi0vL1czQy8vRFREIFNWRyAxLjEvL0VOIiAiaHR0cDovL3d3dy53My5vcmcvR3JhcGhpY3MvU1ZHLzEuMS9EVEQvc3ZnMTEuZHRkIj4NCjxzdmcgdmVyc2lvbj0iMS4xIiBpZD0iQ2FwYV8xIiB4bWxucz0iaHR0cDovL3d3dy53My5vcmcvMjAwMC9zdmciIHhtbG5zOnhsaW5rPSJodHRwOi8vd3d3LnczLm9yZy8xOTk5L3hsaW5rIiB4PSIwcHgiIHk9IjBweCINCgkgd2lkdGg9Ijk4NS4zMzNweCIgaGVpZ2h0PSI5ODUuMzM0cHgiIHZpZXdCb3g9IjAgMCA5ODUuMzMzIDk4NS4zMzQiIHN0eWxlPSJlbmFibGUtYmFja2dyb3VuZDpuZXcgMCAwIDk4NS4zMzMgOTg1LjMzNDsiDQoJIHhtbDpzcGFjZT0icHJlc2VydmUiPg0KPGc+DQoJPHBhdGggZD0iTTg2OC41NjUsNDkyLjhjLTQuNCwyMi4xMDEtMjQsMzguMi00Ny41LDM5LjJjLTcuNCwwLjMtMTMuNyw1LjctMTUuMTAxLDEzYy0xLjUsNy4zLDIuMiwxNC43LDguOSwxNy44DQoJCWMyMS4zLDEwLDMzLjIsMzIuNCwyOC43LDU0LjVsLTQuMiwyMWMtNS41LDI3LjctMzYuMTAxLDQ1LTYyLjksMzguNGMtNy41LTEuOC0xNS4yLTMuMi0yMi44LTQuN2MtMTEuMi0yLjItMjIuNC00LjUtMzMuNi02LjcNCgkJYy0xNC44MDEtMy0yOS42MDEtNS44OTktNDQuNC04Ljg5OWMtMTcuNi0zLjUtMzUuMy03LjEwMS01Mi45LTEwLjYwMWMtMTkuNjk5LTQtMzkuMzk5LTcuODk5LTU5LjEtMTEuODk5DQoJCWMtMjEtNC4yLTQyLjEtOC40LTYzLjEtMTIuN2MtMjEuNjAxLTQuMy00My4yLTguNy02NC43LTEzYy0yMS40LTQuMy00Mi43LTguNjAxLTY0LjEwMS0xMi45Yy0yMC4zOTktNC4xLTQwLjgtOC4yLTYxLjE5OS0xMi4zDQoJCWMtMTguNy0zLjctMzcuMy03LjUtNTYtMTEuMmMtMTYuMi0zLjItMzIuNC02LjUtNDguNS05LjdjLTEyLjktMi42LTI1LjgtNS4xOTktMzguOC03LjhjLTguOS0xLjgtMTcuODAxLTMuNi0yNi43LTUuMzk5DQoJCWMtNC4xMDEtMC44MDEtOC4yLTEuNy0xMi4zLTIuNWMtMC4yLDAtMC40LTAuMTAxLTAuNjAxLTAuMTAxYzIuMiwxMC40LDEuMiwyMS41LTMuNiwzMS45Yy0xMC4xMDEsMjEuOC0zMy42MDEsMzMuMi01Ni4yLDI4LjgNCgkJYy02LjctMS4zLTE0LDEuMi0xNi45LDcuNGwtOSwxOS41Yy0yLjg5OSw2LjE5OSwwLDEzLjM5OSw1LjMwMSwxNy42OTljMSwwLjgwMSw3MjEuOCwzMzMuMTAxLDcyMi45OTksMzMzLjQNCgkJYzYuNywxLjMsMTQtMS4yLDE2LjktNy40bDktMTkuNWMyLjktNi4xOTksMC0xMy4zOTktNS4zLTE3LjY5OWMtMTgtMTQuMzAxLTI0LjYwMS0zOS42MDEtMTQuNS02MS40YzEwLjEtMjEuOCwzMy42LTMzLjIsNTYuMi0yOC44DQoJCWM2LjY5OSwxLjMsMTQtMS4yLDE2Ljg5OS03LjRsOS0xOS41YzIuOS02LjIsMC0xMy4zOTktNS4zLTE3LjdjLTE4LTE0LjMtMjQuNi0zOS42LTE0LjUtNjEuMzk5czMzLjYtMzMuMiw1Ni4yLTI4LjgNCgkJYzYuNywxLjMsMTQtMS4yLDE2LjktNy40bDktMTkuNWMyLjg5OS02LjIsMC0xMy40LTUuMzAxLTE3LjdjLTE4LTE0LjMtMjQuNi0zOS42LTE0LjUtNjEuNGMxMC4xMDEtMjEuOCwzMy42MDEtMzMuMTk5LDU2LjItMjguOA0KCQljNi43LDEuMywxNC0xLjIsMTYuOS03LjM5OWw5Ljg5OS0yMS42MDFjMi45LTYuMiwwLjItMTMuNS02LTE2LjM5OWwtMTA3LjY5OS00OS43TDg2OC41NjUsNDkyLjh6Ii8+DQoJPHBhdGggZD0iTTkuNjY1LDQ4NS45YzEuMiwwLjYsNzc5LjMsMTU2LjY5OSw3ODAuNiwxNTYuNjk5YzYuODAxLTAuMywxMy40LTQuNSwxNC43LTExLjFsNC4yLTIxYzEuMy02LjctMy4xLTEzLjEtOS4zLTE2DQoJCWMtMjAuOC05LjgtMzMuMTAxLTMyLjgtMjguNC01Ni40YzQuNy0yMy42LDI1LTQwLjEsNDgtNDEuMWM2LjgtMC4zLDEzLjQtNC41LDE0LjctMTEuMWwzLjEtMTUuNGwxLjEwMS01LjcNCgkJYzEuMy02LjctMy4xMDEtMTMuMS05LjMtMTZjLTIwLjgwMS05LjgtMzMuMTAxLTMyLjgtMjguNC01Ni4zOTljNC43LTIzLjYwMSwyNS00MC4xMDEsNDgtNDEuMTAxYzYuOC0wLjMsMTMuNC00LjUsMTQuNy0xMS4xDQoJCWw0LjItMjFjMS4zLTYuNy0zLjEwMS0xMy4xLTkuMzAxLTE2Yy0yMC44LTkuOC0zMy4xLTMyLjgtMjguMzk5LTU2LjRjNC43LTIzLjYsMjUtNDAuMSw0OC00MS4xYzYuOC0wLjMsMTMuMzk5LTQuNSwxNC43LTExLjENCgkJbDQuNjk5LTIzLjNjMS4zMDEtNi43LTMtMTMuMi05LjY5OS0xNC41YzAsMC03ODEuOS0xNTYuOC03ODIuNy0xNTYuOGMtNS44LDAtMTAuOSw0LjEtMTIuMSw5LjlsLTQuNywyMy4zDQoJCWMtMS4zLDYuNywzLjEsMTMuMSw5LjMsMTZjMjAuOCw5LjgsMzMuMSwzMi44LDI4LjQsNTYuNGMtNC43LDIzLjYtMjUsNDAuMS00OCw0MS4xYy02LjgwMSwwLjMtMTMuNCw0LjUtMTQuNywxMS4xbC00LjIsMjENCgkJYy0xLjMsNi43LDMuMSwxMy4xLDkuMywxNmMyMC44LDkuOCwzMy4xMDEsMzIuOCwyOC40LDU2LjRjLTQuNywyMy42LTI1LDQwLjEtNDgsNDEuMWMtNi44LDAuMy0xMy40LDQuNS0xNC43LDExLjFsLTQuMiwyMQ0KCQljLTEuMyw2LjcsMy4xMDEsMTMuMSw5LjMsMTZjMjAuODAxLDkuOCwzMy4xMDEsMzIuOCwyOC40LDU2LjRjLTQuNywyMy42MDEtMjUsNDAuMTAxLTQ4LDQxLjEwMWMtNi44LDAuMy0xMy40LDQuNS0xNC43LDExLjENCgkJbC00LjIsMjFDLTAuOTM1LDQ3Ni43LDMuNDY0LDQ4Myw5LjY2NSw0ODUuOXogTTY3Ni4xNjUsMjI5LjZjMi43LTEzLjUsMTUuOS0yMi4zLDI5LjQtMTkuNnMyMi4zLDE1LjksMTkuNiwyOS40bC0zMywxNjQuMg0KCQlsLTIwLjMsMTAxLjJjLTIuNCwxMS45LTEyLjgsMjAuMTAxLTI0LjUsMjAuMTAxYy0xLjYwMSwwLTMuMy0wLjItNC45LTAuNWMtMTMuNS0yLjctMjIuMy0xNS45LTE5LjYtMjkuNGwyMi43LTExMi45TDY3Ni4xNjUsMjI5LjYNCgkJeiBNMjI1LjM2NSwxMzkuMWMyLjctMTMuNSwxNS45LTIyLjMsMjkuNC0xOS42czIyLjMsMTUuOSwxOS42LDI5LjRsLTExLjQsNTYuN2wtMTIuODk5LDY0LjNsLTEwLjQsNTEuOGwtMTguNSw5Mi42DQoJCWMtMi4zOTksMTEuOS0xMi44LDIwLjEwMS0yNC41LDIwLjEwMWMtMS42LDAtMy4zLTAuMi00Ljg5OS0wLjVjLTAuNy0wLjEwMS0xLjQtMC4zMDEtMi0wLjVjLTEyLjQtMy42MDEtMjAuMTAxLTE2LjEwMS0xNy41LTI4LjkNCgkJbDMuNjk5LTE4LjdsOS43LTQ4LjRMMjI1LjM2NSwxMzkuMXoiLz4NCjwvZz4NCjxnPg0KPC9nPg0KPGc+DQo8L2c+DQo8Zz4NCjwvZz4NCjxnPg0KPC9nPg0KPGc+DQo8L2c+DQo8Zz4NCjwvZz4NCjxnPg0KPC9nPg0KPGc+DQo8L2c+DQo8Zz4NCjwvZz4NCjxnPg0KPC9nPg0KPGc+DQo8L2c+DQo8Zz4NCjwvZz4NCjxnPg0KPC9nPg0KPGc+DQo8L2c+DQo8Zz4NCjwvZz4NCjwvc3ZnPg0K',
                'public'               =>   true,
                'publicly_queriable'   =>   true,
                'show_ui'              =>   true,
                'show_in_nav_menus'    =>   true,
                'has_archive'          =>   true,
                'rewrite'              =>   array(
                    'slug'       => 'event',
                    'with_front' => false
                ),
                'hierarchical'          =>  false,
                'exclude_from_search'   =>  false,
                'taxonomies'            =>  array('event-categories'),
                'supports'              =>  array('title', 'revisions', 'editor', 'thumbnail'),
            )
        );

        add_action('manage_posts_extra_tablenav', array($this, 'tablenavButtons'));

        $this->addTableColumn('cb', '<input type="checkbox">');
        $this->addTableColumn('title', __('Title'));
        $this->addTableColumn('location', __('Location'), true, function ($column, $postId) {
            $locationId = get_field('location', $postId);
            if (!isset($locationId[0])) {
                echo 'n/a';
                return;
            }
            echo '<a href="' . get_edit_post_link($locationId[0]) . '">' . get_the_title($locationId[0]) . '</a>';
        });
        $this->addTableColumn('contact', __('Contact'), true, function ($column, $postId) {
            $contactId = get_field('contacts', $postId);
            if (!isset($contactId[0])) {
                echo 'n/a';
                return;
            }

            echo '<a href="' . get_edit_post_link($contactId[0]) . '">' . get_the_title($contactId[0]) . '</a>';
        });

        $this->addTableColumn('acceptAndDeny', __('Accepted/Denied'), true, function($column, $postId) {
            $metaAccepted = get_post_meta($postId, 'accepted');
            $first = '';
            $second = '';
            if($metaAccepted[0] == 1)
                $first = 'hiddenElement';
            else if($metaAccepted[0] == -1)
                $second = 'hiddenElement';
            echo '<a href="#" class="accept button-primary ' . $first . '" postid="' . $postId . '">' . __('Accept') . '</a>
            <a href="#" class="deny button-primary ' . $second . '" postid="' . $postId . '">' . __('Deny') . '</a>';
        });

        $this->addPostAction();
    }

    /**
     * Add buttons to start parsing xcap and Cbis
     * @return void
     */
    public function tablenavButtons($which)
    {
        global $current_screen;

        if ($current_screen->id != 'edit-event' || $which != 'top') {
            return;
        }

        echo '<div class="alignleft actions">';
            echo '<a href="' . admin_url('options.php?page=import-events')
                    . '" class="button-primary" id="post-query-submit">Import XCAP</a>';
            echo '<a href="' . admin_url('options.php?page=import-cbis-events')
                    . '" class="button-primary" id="post-query-submit">Import CBIS</a>';
        echo '</div>';
    }


}
