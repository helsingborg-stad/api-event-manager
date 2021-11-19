<?php

namespace HbgEventImporter\MiddleLayer;

class Languages extends \HbgEventImporter\MiddleLayer\ApiRequest
{
    public $singularName = "language";
    public $pluralName = "languages";

    public function __construct()
    {
        parent::__construct($this->singularName, $this->pluralName);

        if ($this->isCdnSyncEnabled) {
            add_action('pll_add_language', array($this, 'saveLanguage'), 10, 1);
            add_action('pll_update_language', array($this, 'saveLanguage'), 10, 1);
            add_action('delete_' . $this->singularName, array($this, 'deleteItem'), 10, 1);
        }
    }

    public function saveLanguage($args)
    {
        $termId = $args['lang_id'] ?? null;

        if (!$termId) {
            $termObject = get_term_by('slug', $args['slug'], 'language');
            if (empty($termObject->term_id)) {
                return;
            }
            $termId = $termObject->term_id;
        }

        $body = [
          'id'  => (int) $termId,
          "name" => $args['name'],
          "slug" => $args['slug'],
          "locale" => $args['locale']
        ];
        $body = wp_json_encode($body);
        $this->post($body);
    }
}
