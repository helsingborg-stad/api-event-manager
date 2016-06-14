<?php

namespace HbgEventImporter\PostTypes;

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
                'menu_icon'            => 'data:image/svg+xml;base64,PHN2ZyB4bWxucz0iaHR0cDovL3d3dy53My5vcmcvMjAwMC9zdmciIHdpZHRoPSI1MTIiIGhlaWdodD0iNTEyIiB2aWV3Qm94PSIwIDAgNDU1LjU2MyA0NTUuNTYzIj48cGF0aCBkPSJNNDU0LjU3NSA3OS4xMDJsLTI4Ljc5Mi02Ny45NzctMTUuODk1IDYuNzMyTDQwMi4zNDIgMGwtMjMuOTggMTAuMTMyIDMxLjk2NSA3NS42NSAyMS41MS05LjA4NyAyLjczOCA2LjQ2N3YzOS42ODdjLTExLjkyOC0xNC42ODUtMjkuMTktMjcuOTQtNTEuMjkyLTM5LjA5NC00MS44Mi0yMS4xLTk3LjA0My0zMi43MTgtMTU1LjUtMzIuNzE4UzExNC4xIDYyLjY1OCA3Mi4yOCA4My43NThjLTIyLjEgMTEuMTUtMzkuMzYzIDI0LjQxLTUxLjI5IDM5LjA5VjgzLjE2NGwyLjczOC02LjQ2NyAyMS41MSA5LjA4OEw3Ny4yIDEwLjEzNCA1My4yMiAwbC03LjU0MyAxNy44Ni0xNS44OTctNi43MzRMLjk5IDc5LjEwMnYyNDguODE2aC4yOTJWMzI5YzAgMzYuNDcgMjQuNzgyIDcwLjAyNyA2OS43ODIgOTQuNDk1IDI5LjMwNiAxNS45MzYgNjUuMTggMjYuODggMTA0LjE1NSAzMi4wNjd2LTMwLjM0YzAtMjkuMDMgMjMuNTMtNTIuNTYzIDUyLjU2LTUyLjU2M3M1Mi41NjMgMjMuNTMgNTIuNTYzIDUyLjU2djMwLjM0YzM4Ljk3Ny01LjE4NiA3NC44NS0xNi4xMyAxMDQuMTU1LTMyLjA2NSA0NS0yNC40NjggNjkuNzgtNTguMDI1IDY5Ljc4LTk0LjQ5NXYtMS4wOGguMjk0Vjc5LjEwMnpNMTYyLjA4IDI0MC4wMTRsMTguMzItOC4wMjQtOS42MS0yMS45NGMxOC4yNjItMi42NjMgMzcuNDAzLTQuMDQ4IDU2Ljk5Mi00LjA0OHMzOC43MyAxLjM4NSA1Ni45OTIgNC4wNWwtOS42MSAyMS45MzggMTguMzIgOC4wMjQgMTEuNTY1LTI2LjQwOGMyMi41OCA0LjY2NCA0My40MTYgMTEuNDA2IDYxLjU4IDIwLjAyNS0zNy4wMjMgMTguMzI1LTg2LjI0OCAyOC40MS0xMzguODUgMjguNDEtNTIuNjA0IDAtMTAxLjgzLTEwLjA4NS0xMzguODUtMjguNDA4IDE4LjE2Mi04LjYyIDM5LTE1LjM2IDYxLjU4LTIwLjAyNGwxMS41NyAyNi40MDh6bTIyNS4wNTgtMTguMzRhMjExLjYwNCAyMTEuNjA0IDAgMCAwLTYuOTktMy43Yy0xOS42Mi05Ljg5Ny00Mi4yOTUtMTcuNjU0LTY2LjkxNS0yMy4wNWwxNy42NjgtNDAuMzRjMTQuNTE4IDQuMzIyIDI4LjAzNSA5LjU5IDQwLjIzOCAxNS43NDYgMTcuMDA1IDguNTggMzAuMzkgMTguMzIgMzkuNzgyIDI4Ljc2NS01LjU0OCA3Ljg4LTEzLjU2OCAxNS41MDctMjMuNzgyIDIyLjU4em0tOTQuMDY2LTMwLjU3QzI3Mi4yMDggMTg3Ljc1IDI1MC4yNSAxODYgMjI3Ljc4MiAxODZzLTQ0LjQyNiAxLjc0OC02NS4yOSA1LjEwM2wtMTguMjM4LTQxLjY0YzI1Ljg2Ni01Ljg2OCA1NC4xOTgtOC45NjMgODMuNTI4LTguOTYzczU3LjY2MiAzLjA5NSA4My41MjggOC45NjRsLTE4LjIzOCA0MS42NHptLTE1MC43NDIgMy44MmMtMjQuNjIgNS4zOTUtNDcuMjk2IDEzLjE1Mi02Ni45MTQgMjMuMDVhMjEwLjYwNCAyMTAuNjA0IDAgMCAwLTYuOTkgMy43Yy0xMC4yMTUtNy4wNy0xOC4yMzUtMTQuNy0yMy43ODQtMjIuNTggOS4zOTItMTAuNDQzIDIyLjc3Ny0yMC4xODUgMzkuNzgzLTI4Ljc2NCAxMi4yMDMtNi4xNTcgMjUuNzItMTEuNDI0IDQwLjIzNi0xNS43NDVsMTcuNjcgNDAuMzM4em0tOTAuMzc2LTU1Ljc5YzQuNzctNC44MiAxMC4yOC05LjQzNyAxNi40NjQtMTMuOCA1LjcxMi0zLjk1NCAxMi4xMDQtNy43MzYgMTkuMTQ1LTExLjI4OCAzNy4xNy0xOC43NTQgODYuOTctMjkuMDgyIDE0MC4yMi0yOS4wODJzMTAzLjA1IDEwLjMyOCAxNDAuMjIgMjkuMDgyYzcuMDQgMy41NTIgMTMuNDM0IDcuMzM0IDE5LjE0NiAxMS4yOSA2LjE4IDQuMzYgMTEuNjkgOC45NzggMTYuNDYyIDEzLjc5OCAxMC42MDIgMTEuMDA1IDE2LjMxNiAyMi43NjcgMTYuMzE2IDM0LjM2OCAwIDEuOTI3LS4xNjMgMy44NTctLjQ3NCA1Ljc5LTEwLjM2NS05LjgyOC0yMy41My0xOC44Ni0zOS4zMDctMjYuODE4LTEyLjU2Ni02LjM0LTI2LjM5LTExLjgtNDEuMTctMTYuMzI3bDcuNTA1LTE3LjEzMy0xOC4zMi04LjAyNC04LjY5NSAxOS44NTRjLTI4LjM2LTYuNzctNTkuNTA2LTEwLjM0My05MS42ODYtMTAuMzQzLTMyLjE4IDAtNjMuMzI0IDMuNTczLTkxLjY4NSAxMC4zNDVMMTI3LjQgMTEwLjk5bC0xOC4zMiA4LjAyNSA3LjUwNCAxNy4xMzNjLTE0Ljc4IDQuNTI3LTI4LjYwMyA5Ljk4Ny00MS4xNjggMTYuMzI3LTE1Ljc3NyA3Ljk2LTI4Ljk0MiAxNi45OS0zOS4zMDggMjYuODE3YTM2LjM4MyAzNi4zODMgMCAwIDEtLjQ3Mi01Ljc4OGMtLjAwMi0xMS42MDIgNS43MTItMjMuMzY0IDE2LjMxNC0zNC4zNjh6IiBmaWxsPSIjRkZGIi8+PC9zdmc+',
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
                'supports'             => array('title', 'revisions', 'editor', 'thumbnail')
            )
        );

        $this->addTableColumn('cb', '<input type="checkbox">');

        $this->addTableColumn('title', __('Title'));
        $this->addTableColumn('name', __('Address'), true, function ($column, $postId) {
            echo get_post_meta($postId, 'formattedAddress', true) ? get_post_meta($postId, 'formattedAddress', true) : 'n/a';
        });
        $this->addTableColumn('date', __('Date'));
    }


}
