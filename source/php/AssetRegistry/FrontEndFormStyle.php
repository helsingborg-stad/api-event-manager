<?php

namespace EventManager\AssetRegistry;

use EventManager\AssetRegistry\RegisterAsset;

class FrontEndFormStyle extends RegisterAsset
{
    public function getHandle(): string
    {
        return 'event-manager-frontend-form';
    }

    public function getFilename(): string
    {
        return 'css/frontend-acf-form.css';
    }
}
