<?php

namespace EventManager\AssetRegistry;

use EventManager\Helper\Asset;

class FrontEndFormStyle extends Asset
{
    public function getHandle(): string|bool
    {
        return 'event-manager-frontend-form';
    }

    public function getFilename(): string
    {
        return 'css/frontend-acf-form.css';
    }
}
