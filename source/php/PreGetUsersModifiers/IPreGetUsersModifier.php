<?php

namespace EventManager\PreGetUsersModifiers;

use EventManager\Helper\Hookable;
use WP_User_Query;

interface IPreGetUsersModifier extends Hookable
{
    public function modify(WP_User_Query $query): WP_User_Query;
}
