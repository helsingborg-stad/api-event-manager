<?php

namespace HbgEventImporter\MiddleLayer;

class GuideGroups extends \HbgEventImporter\MiddleLayer\SyncManager
{
    public $singularName = "guidegroup";
    public $pluralName = "guidegroups";

    public function __construct()
    {
        parent::__construct($this->singularName, $this->pluralName);

        if ($this->isCdnSyncEnabled) {
            add_action('create_' . $this->singularName, array($this, 'saveEmbeddedItem'), 10, 2);
            add_action('edited_' . $this->singularName, array($this, 'saveEmbeddedItem'), 10, 2);
            add_action('delete_' . $this->singularName, array($this, 'deleteItem'), 10, 1);
        }
    }
}
