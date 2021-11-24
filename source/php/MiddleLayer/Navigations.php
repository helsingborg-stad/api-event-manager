<?php

namespace HbgEventImporter\MiddleLayer;

class Navigations extends \HbgEventImporter\MiddleLayer\SyncManager
{
    public $singularName = "navigation";
    public $pluralName = "navigations";

    public function __construct()
    {
        parent::__construct($this->singularName, $this->pluralName);

        if ($this->isCdnSyncEnabled) {
            add_action('create_' . $this->singularName, array($this, 'saveItem'), 10);
            add_action('edited_' . $this->singularName, array($this, 'saveItem'), 10);
            add_action('pre_delete_term', array($this, 'deleteNavigation'), 10, 2);
        }
    }

    public function deleteNavigation($termId, $taxonomy)
    {
        if ($taxonomy !== $this->singularName) {
            return;
        }

        $data = $this->getRestResponse($termId);
        $groupId = $data['user_groups']['id'] ?? null;
        $lang = $data['lang'] ?? null;
        if (empty($data) || empty($groupId) || empty($lang)) {
            return;
        }

        $path = sprintf('%s/%s/%s', $groupId, $lang, $termId);
        $this->deleteItem($path);
    }

    public function startPopulate()
    {
        $terms = get_terms(array(
          'taxonomy' => $this->singularName,
          'hide_empty' => false,
        ));

        if (!empty($terms) && is_array($terms)) {
            foreach ($terms as $term) {
                $this->saveItem($term->term_id);
            }
        }
    }
}
