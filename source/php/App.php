<?php

namespace EventManager;

use EventManager\Helper\DIContainer\DIContainer;
use EventManager\Helper\HooksRegistrar\HooksRegistrarInterface;

class App
{
    private DIContainer $diContainer;
    private HooksRegistrarInterface $hooksRegistrar;

    public function __construct(DIContainer $diContainer, HooksRegistrarInterface $hooksRegistrar)
    {
        $this->diContainer    = $diContainer;
        $this->hooksRegistrar = $hooksRegistrar;
    }

    public function registerHooks()
    {
        $hookableClasses = [
            \EventManager\Helper\LoadTextDomain::class,
            \EventManager\Helper\RegisterAcfExportManager::class,
            \EventManager\HideUnusedAdminPages::class,
            \EventManager\PostTypes\Event::class,
            \EventManager\Taxonomies\Audience::class,
            \EventManager\Taxonomies\Organization::class,
            \EventManager\Taxonomies\Keyword::class,
            \EventManager\ApiResponseModifiers\Event::class,
            \EventManager\SetPostTermsFromContent\SetPostTermsFromContent::class,
            \EventManager\Modifiers\ModifyPostContentBeforeReadingTags::class,
            \EventManager\CleanupUnusedTags\CleanupUnusedTags::class,
            \EventManager\Modules\FrontendForm\Register::class,
            \EventManager\Modifiers\DisableGutenbergEditor::class
        ];

        foreach ($hookableClasses as $hookableClass) {
            $hookableClassInstance = $this->diContainer->get($hookableClass);
            $this->hooksRegistrar->register($hookableClassInstance);
        }
    }
}
