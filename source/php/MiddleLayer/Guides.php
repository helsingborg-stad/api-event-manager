<?php

namespace HbgEventImporter\MiddleLayer;

class Guides extends \HbgEventImporter\MiddleLayer\SyncManager
{
    public $singularName = "guide";
    public $pluralName = "guides";

    public function __construct()
    {
        parent::__construct($this->singularName, $this->pluralName);

        if ($this->isCdnSyncEnabled) {
            if (class_exists('ACF')) {
                add_action('acf/save_post', array($this, 'savePost'), 99, 1);
            }
            add_action('delete_post', array($this, 'deletePost'), 10);
        }
    }
}
