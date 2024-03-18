<?php

namespace EventManager;

use EventManager\Helper\HooksRegistrar\HooksRegistrarInterface;
use EventManager\Services\WPService\WPService;
use EventManager\SetPostTermsFromContent\SetPostTermsFromContent;

class App
{
    private WPService $wpService;

    public function __construct(WPService $wpService)
    {
        $this->wpService = $wpService;
    }

    public function registerHooks(HooksRegistrarInterface $hooksRegistrar)
    {
        $tagReader = new \EventManager\TagReader\TagReader();

        $hooksRegistrar
            ->register(new \EventManager\HideUnusedAdminPages($this->wpService))
            ->register(new \EventManager\PostTypes\Event($this->wpService))
            ->register(new \EventManager\Taxonomies\Audience($this->wpService))
            ->register(new \EventManager\Taxonomies\Organization($this->wpService))
            ->register(new \EventManager\Taxonomies\Keyword($this->wpService))
            ->register(new \EventManager\ApiResponseModifiers\Event())
            ->register(new SetPostTermsFromContent($tagReader, $this->wpService, 'event', 'keyword'))
            ->register(new \EventManager\Modifiers\ModifyPostContentBeforeReadingTags($this->wpService))
            ->register(new \EventManager\CleanupUnusedTags\CleanupUnusedTags('keyword', $this->wpService))
            ->register(new \EventManager\Modules\FrontendForm\Register($this->wpService))
            ->register(new \EventManager\FieldSettingHidePublic($this->wpService))
            ->register(new \EventManager\AssetRegistry\FrontEndFormStyle($this->wpService));
    }
}
