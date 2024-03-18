<?php

namespace EventManager;

use EventManager\Helper\DIContainer\DIContainer;
use EventManager\Helper\HooksRegistrar\HooksRegistrarInterface;
use EventManager\Services\WPService\WPService;
use EventManager\TableColumns\PostMetaTableColumn;
use EventManager\TableColumns\PostTableColumns\OpenStreetMapTableColumn;
use EventManager\TableColumns\PostTableColumns\PostTableColumnsManager;
use EventManager\TableColumns\PostTableColumns\TermNameTableColumn;
use EventManager\TableColumns\PostTermTableColumn;

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

        $this->hooksRegistrar->register($this->getPostTableColumnsManager());
    }

    private function getPostTableColumnsManager(): PostTableColumnsManager
    {
        $wpService = $this->diContainer->get(WPService::class);
        $columns   = [
            new OpenStreetMapTableColumn(__('Location', 'api-event-manager'), 'location', $wpService),
            new TermNameTableColumn(__('Organizer', 'api-event-manager'), 'organization', $wpService),
        ];

        $manager = new PostTableColumnsManager($wpService);

        foreach ($columns as $column) {
            $manager->register($column);
        }

        return $manager;
    }
}
