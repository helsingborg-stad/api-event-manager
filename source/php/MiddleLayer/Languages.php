<?php

namespace HbgEventImporter\MiddleLayer;

class Languages extends \HbgEventImporter\MiddleLayer\ApiRequest
{
    public function __construct()
    {
        parent::__construct("languages");

        if ($this->isCdnSyncEnabled) {
            add_action('pll_add_language', array($this, 'saveLanguage'), 10, 1);
            add_action('pll_update_language', array($this, 'saveLanguage'), 10, 1);
            add_action('delete_language', array($this, 'deleteLanguage'), 10, 4);
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

    public function deleteLanguage($term)
    {
        $this->delete($term);
    }
}
