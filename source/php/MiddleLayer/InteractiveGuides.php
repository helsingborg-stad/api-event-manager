<?php

namespace HbgEventImporter\MiddleLayer;

class InteractiveGuides extends \HbgEventImporter\MiddleLayer\SyncManager
{
    public $singularName = "interactive_guide";
    public $pluralName = "interactive_guides";

    public function __construct()
    {
        parent::__construct($this->singularName, $this->pluralName);

        if ($this->isCdnSyncEnabled) {
            add_action('save_post_' . $this->singularName, array($this, 'savePost'), 10, 3);
            add_action('delete_post', array($this, 'deletePost'), 10);
        }
    }
}
