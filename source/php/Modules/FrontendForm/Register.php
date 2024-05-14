<?php

namespace EventManager\Modules\FrontendForm;

use EventManager\Modules\Module;

class Register extends Module
{
    public function getModuleName(): string
    {
        return 'FrontendForm';
    }

    public function getModulePath(): string
    {
        if (!defined('EVENT_MANAGER_PATH')) {
            return '';
        }

        return constant('EVENT_MANAGER_PATH') . 'source/php/Modules/FrontendForm/';
    }
}
