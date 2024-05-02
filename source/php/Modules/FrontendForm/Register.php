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
        return EVENT_MANAGER_PATH . 'source/php/Modules/FrontendForm/';
    }
}
