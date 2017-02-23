<?php

namespace HbgEventImporter\Helper;

class AcfSync
{
    public function __construct()
    {
        //add_action('save_post', array($this,'saveAcfPHP'));
    }

    public function savePath()
    {
    }

    public function saveAcfPHP($post)
    {
        var_dump($post);
    }
}
