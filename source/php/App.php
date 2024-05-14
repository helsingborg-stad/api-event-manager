<?php

namespace EventManager;

use AcfService\AcfService;
use EventManager\CleanupUnusedTags\CleanupUnusedTags;
use EventManager\PostTableColumns\Column as PostTableColumn;
use EventManager\PostTableColumns\ColumnCellContent\NestedMetaStringCellContent;
use EventManager\PostTableColumns\ColumnCellContent\TermNameCellContent;
use EventManager\PostTableColumns\ColumnSorters\MetaStringSort;
use EventManager\PostTableColumns\ColumnSorters\NestedMetaStringSort;
use EventManager\PostTableColumns\Helpers\GetNestedArrayStringValueRecursive;
use EventManager\PostToSchema\Mappers\StringToEventSchemaMapper;
use EventManager\PostToSchema\PostToEventSchema\PostToEventSchema;
use EventManager\SetPostTermsFromContent\SetPostTermsFromContent;
use EventManager\TagReader\TagReader;
use EventManager\ApiResponseModifiers\EventResponseModifier;
use EventManager\ContentExpirationManagement\ExpiredEvents;
use EventManager\CronScheduler\CronSchedulerInterface;
use EventManager\PostToSchema\PostToEventSchema\Commands\Helpers\CommandHelpers;
use EventManager\HooksRegistrar\HooksRegistrarInterface;
use WpService\WpService;

class App
{
    public function __construct(
        private string $textDomain,
        private WpService $wpService,
        private AcfService $acfService,
        private HooksRegistrarInterface $hooksRegistrar,
        private CronSchedulerInterface $cronScheduler
    ) {
    }

    public function loadPluginTextDomain(): void
    {
        $loadTextDomain = new \EventManager\Helper\LoadTextDomain($this->textDomain, $this->wpService);
        $this->hooksRegistrar->register($loadTextDomain);
    }

    public function setupAcfExportManager(): void
    {
        $acfExportManager = new \EventManager\Helper\RegisterAcfExportManager($this->wpService);
        $this->hooksRegistrar->register($acfExportManager);
    }

    public function setupPluginSettingsPage(): void
    {
        $adminSettingsPage = new \EventManager\Settings\AdminSettingsPage($this->wpService, $this->acfService);
        $this->hooksRegistrar->register($adminSettingsPage);
    }

    public function convertRestApiPostsToSchemaObjects(): void
    {
        $stringToEventSchemaMapper         = new StringToEventSchemaMapper();
        $postToSchemaAdapterCommandHelpers = new CommandHelpers();
        $postToSchemaAdapter               = new PostToEventSchema($stringToEventSchemaMapper, $this->wpService, $this->acfService, $postToSchemaAdapterCommandHelpers);
        $eventResponseModifier             = new EventResponseModifier($postToSchemaAdapter, $this->wpService);
        $this->hooksRegistrar->register($eventResponseModifier);
    }

    public function setupCleanupUnusedTags(): void
    {
        $cleanUpUnusedTags = new CleanupUnusedTags('keyword', $this->wpService);
        $this->hooksRegistrar->register($cleanUpUnusedTags);
    }

    public function setPostTermsFromPostContent(): void
    {
        $tagReader               = new TagReader();
        $modifyPostcontentBefore = new \EventManager\Modifiers\ModifyPostContentBeforeReadingTags($this->wpService);
        $setPostTermsFromContent = new SetPostTermsFromContent('event', 'keyword', $tagReader, $this->wpService);

        $this->hooksRegistrar->register($setPostTermsFromContent);
        $this->hooksRegistrar->register($modifyPostcontentBefore);
    }

    public function cleanUpExpiredEvents(): void
    {
        $cleanupExpiredEvents = $this->wpService->getOption('options_cleanup_cleanupExpiredEvents') === '1';

        if (!$cleanupExpiredEvents) {
            return;
        }

        $deleteExpiredPostsAfter   = strtotime($this->wpService->getOption('options_cleanup_deleteExpiredPostsAfter', '-1 month'));
        $eventGracePeriodTimestamp = strtotime('-1 day');

        $eventsInGracePeriod      = new ExpiredEvents($eventGracePeriodTimestamp, $this->wpService, $this->acfService);
        $eventsReadyForDeletion   = new ExpiredEvents($deleteExpiredPostsAfter, $this->wpService, $this->acfService);
        $adminNotifyExpiredEvents = new \EventManager\ContentExpirationManagement\AdminNotifyExpiredPost([$eventsInGracePeriod], $this->wpService);
        $deleteExpiredEvents      = new \EventManager\ContentExpirationManagement\DeleteExpiredPosts([$eventsReadyForDeletion], $this->wpService);

        $this->cronScheduler->addEvent(new \EventManager\CronScheduler\CronEvent('daily', 'event_manager_delete_expired_events_cron', [$deleteExpiredEvents, 'delete']));

        $this->hooksRegistrar->register($adminNotifyExpiredEvents);
    }

    public function modifyAdminTablesColumns(): void
    {
        /**
         * Table Columns
         */
        $organizationCellContent =  new TermNameCellContent('organization', $this->wpService);
        $organizationCellSort    = new MetaStringSort('organization');
        $organizationColumn      = new PostTableColumn(__('Organizer', 'api-event-manager'), 'organization', $organizationCellContent, $organizationCellSort);
        $locationCellContent     =  new NestedMetaStringCellContent('location.address', $this->wpService, new GetNestedArrayStringValueRecursive());
        $locationCellSort        = new NestedMetaStringSort('location.address', $this->wpService, new GetNestedArrayStringValueRecursive());
        $locationColumn          = new PostTableColumn(__('Location', 'api-event-manager'), 'location.address', $locationCellContent, $locationCellSort);
        $postTableColumnsManager = new \EventManager\PostTableColumns\Manager(['event'], $this->wpService);
        $postTableColumnsManager->register($organizationColumn);
        $postTableColumnsManager->register($locationColumn);
        $this->hooksRegistrar->register($postTableColumnsManager);


        /**
         * Table Content
         */
        $this->hooksRegistrar->register(
            new \EventManager\PreGetPostModifiers\LimitEventTableResultsByUserRole($this->wpService, $this->acfService)
        );
        $this->hooksRegistrar->register(
            new \EventManager\PreGetUsersModifiers\ListOnlyUsersFromSameOrganization($this->wpService, $this->acfService)
        );
        /**
         * Table filters
         */
        $eventPostStatusFilter = new \EventManager\PostTableFilters\EventPostStatusFilter($this->wpService);
        $eventPostStatusFilter->hideViewsFromEventTable(); // Hide views from table to avoid confusion.
        $postTableFiltersRegistrar = new \EventManager\PostTableFilters\Registrar([
        $eventPostStatusFilter
        ], $this->wpService);

        $this->hooksRegistrar->register($postTableFiltersRegistrar);
    }

    public function disableGutenbergEditor(): void
    {
        $disableGutenbergEditor = new \EventManager\Modifiers\DisableGutenbergEditor($this->wpService);
        $hideUnusedAdminPages   = new \EventManager\HideUnusedAdminPages($this->wpService);
        $this->hooksRegistrar->register($disableGutenbergEditor);
        $this->hooksRegistrar->register($hideUnusedAdminPages);
    }

    public function setupPostTypes(): void
    {
        $eventPostType = new \EventManager\PostTypes\Event($this->wpService);
        $this->hooksRegistrar->register($eventPostType);
    }

    public function setupTaxonomies(): void
    {
        $this->hooksRegistrar->register(new \EventManager\Taxonomies\Audience($this->wpService));
        $this->hooksRegistrar->register(new \EventManager\Taxonomies\Organization($this->wpService));
        $this->hooksRegistrar->register(new \EventManager\Taxonomies\Keyword($this->wpService));
    }

    public function setupUserRoles(): void
    {
        $userRoles = [
            new \EventManager\User\Role('organization_administrator', 'Organization Administrator', ['read']),
            new \EventManager\User\Role('organization_member', 'Organization Member', ['read']),
            new \EventManager\User\Role('pending_organization_member', 'Pending Organization Member', ['read']),
        ];

        $this->hooksRegistrar->register(new \EventManager\User\RoleRegistrar($userRoles, $this->wpService));
    }

    public function setupUserCapabilities(): void
    {
        $postBelongsToSameOrganizationAsUser = new \EventManager\User\UserHasCap\Implementations\Helpers\PostBelongsToSameOrganizationAsUser($this->wpService, $this->acfService);
        $usersBelongsToSameOrganization      = new \EventManager\User\UserHasCap\Implementations\Helpers\UsersBelongsToSameOrganization($this->acfService);
        $userBelongsToOrganization           = new \EventManager\User\UserHasCap\Implementations\Helpers\UserBelongsToOrganization($this->acfService);

        $capabilities = [
            new \EventManager\User\UserHasCap\Implementations\EditEvents(),
            new \EventManager\User\UserHasCap\Implementations\EditEvent($postBelongsToSameOrganizationAsUser, $this->wpService),
            new \EventManager\User\UserHasCap\Implementations\EditOthersEvents(),
            new \EventManager\User\UserHasCap\Implementations\PublishEvent(),
            new \EventManager\User\UserHasCap\Implementations\DeleteEvent($postBelongsToSameOrganizationAsUser),
            new \EventManager\User\UserHasCap\Implementations\ListUsers(),
            new \EventManager\User\UserHasCap\Implementations\EditUsers(),
            new \EventManager\User\UserHasCap\Implementations\EditUser(),
            new \EventManager\User\UserHasCap\Implementations\DeleteUser($usersBelongsToSameOrganization),
            new \EventManager\User\UserHasCap\Implementations\PromoteUsers(),
            new \EventManager\User\UserHasCap\Implementations\PromoteUser($usersBelongsToSameOrganization),
            new \EventManager\User\UserHasCap\Implementations\PromoteUserToRole(),
            new \EventManager\User\UserHasCap\Implementations\CreateUsers(),
            new \EventManager\User\UserHasCap\Implementations\ManageOrganizations(),
            new \EventManager\User\UserHasCap\Implementations\EditOrganizations(),
            new \EventManager\User\UserHasCap\Implementations\EditOrganization($userBelongsToOrganization),
        ];

        $this->hooksRegistrar->register(new \EventManager\User\UserHasCap\Registrar($capabilities, $this->wpService));
        $this->hooksRegistrar->register(new \EventManager\CustomUserCapabilities\PromoteUserToRole($this->wpService));
    }

    public function setupFrontendForm(): void
    {
        $frontendForm      = new \EventManager\Modules\FrontendForm\Register($this->wpService);
        $frontendFormStyle = new \EventManager\AssetRegistry\FrontEndFormStyle($this->wpService);
        $this->hooksRegistrar->register($frontendForm);
        $this->hooksRegistrar->register($frontendFormStyle);
    }

    public function setupAcfFieldContentModifiers(): void
    {
        $acfFieldContentModifierRegistrar = new \EventManager\AcfFieldContentModifiers\Registrar([
            new \EventManager\AcfFieldContentModifiers\FilterAcfAudienceSelectField('field_65a52a6374b0c', $this->wpService),
            new \EventManager\AcfFieldContentModifiers\FilterAcfOrganizerSelectField('field_65a4f6af50302', $this->wpService, $this->acfService)
        ], $this->wpService);

        $this->hooksRegistrar->register($acfFieldContentModifierRegistrar);
    }

    public function setupAcfSavePostActions(): void
    {
        $acfSavepostRegistrar = new \EventManager\AcfSavePostActions\Registrar([
            new \EventManager\AcfSavePostActions\SetPostTermsFromField('organization', 'organization', $this->wpService, $this->acfService),
            new \EventManager\AcfSavePostActions\SetPostTermsFromField('audience', 'audience', $this->wpService, $this->acfService),
        ], $this->wpService);

        $this->hooksRegistrar->register($acfSavepostRegistrar);
    }

    public function setupFeatureToShowOrHideAcfFieldsOnFrontendAndInAdmin(): void
    {
        $fieldSettingHidePublic = new \EventManager\FieldSettingHidePublic($this->wpService, $this->acfService);
        $fieldSettingHidePublic->addHooks();
        $fieldSettingHidePrivate = new \EventManager\FieldSettingHidePrivate($this->wpService, $this->acfService);
        $fieldSettingHidePrivate->addHooks();
    }

    public function setupNotifications(): void
    {
        $userAddedToOrganizationEvent          = new \EventManager\Notifications\Events\UserAddedToOrganization($this->wpService);
        $emailNotificationSender               = new \EventManager\NotificationServices\EmailNotificationService($this->wpService);
        $memberAddedToOrganizationNotification = new \EventManager\Notifications\MemberAddedToOrganization($emailNotificationSender, $this->wpService);
        $pendingEventCreatedNotification       = new \EventManager\Notifications\PendingEventCreated($emailNotificationSender, $this->wpService);

        $this->hooksRegistrar->register($userAddedToOrganizationEvent);
        $this->hooksRegistrar->register($memberAddedToOrganizationNotification);
        $this->hooksRegistrar->register($pendingEventCreatedNotification);
    }
}
