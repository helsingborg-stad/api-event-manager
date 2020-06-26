<?php

namespace HbgEventImporter\Roles;

class EventEditor
{
    private $role = 'guide_editor';

    public function __construct()
    {
        add_filter('acf/fields/taxonomy/query', array($this, 'limitOptionsToUserOrganisations'), 10, 2);
    }

    public function limitOptionsToUserOrganisations($args, $field)
    {
        $user = get_user_by('id', get_current_user_id());
        $userOrganisations = get_field('organisation', 'user_' . $user->ID);

        if (
            !in_array($this->role, $user->roles)
            || empty($userOrganisations)
            || $field['key'] !== 'field_589dd138aca7e'
        ) {
            return $args;
        }

        $args['meta_query'] = [
            ['relation' => 'OR',
                [
                    'key' => 'organisation',
                    'value' => $userOrganisations,
                    'compare' => 'LIKE',
                ],
                [
                    'key' => 'organisation',
                    'compare' => 'NOT EXISTS',
                ],
            ]
        ];

        $args['hide_empty'] = false;

        return $args;
    }
}