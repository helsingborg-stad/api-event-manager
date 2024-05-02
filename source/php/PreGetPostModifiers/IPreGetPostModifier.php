<?php

namespace EventManager\PreGetPostModifiers;

use EventManager\HooksRegistrar\Hookable;
use WP_Query;

interface IPreGetPostModifier extends Hookable
{
    public function modify(WP_Query $query): WP_Query;
}
