<?php

namespace EventManager\PostTypes;

use EventManager\Helper\PostType;
use EventManager\PostTypes\Icons\Icon;

class Organization extends PostType
{
    public function getName(): string
    {
        return 'organization';
    }

    public function getArgs(): array
    {
        return [
            'show_in_rest' => true,
            'public'       => true,
            'hierarchical' => false,
            'menu_icon'    => (new Icon('Organization'))->getIcon(),
            'rest_base'    => 'organizations',
            'supports'     => [ 'title', 'revisions' ]
        ];
    }

    public function getLabelSingular(): string
    {
        return 'Organization';
    }

    public function getLabelPlural(): string
    {
        return 'Organizations';
    }
}
