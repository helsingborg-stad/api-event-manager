<?php

namespace EventManager\PostTypes;

use EventManager\Helper\Enqueue;

class FrontEndFormStyle extends Enqueue
{
    public function getFilename(): string
    {
        return 'css/frontend-form.css';
    }
}
