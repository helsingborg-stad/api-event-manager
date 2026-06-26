<?php

namespace EventManager\Organizations;

use AcfService\Contracts\GetField;
use EventManager\HooksRegistrar\Hookable;
use WpService\Contracts\__;
use WpService\Contracts\AddAction;
use WpService\Contracts\AddQueryArg;
use WpService\Contracts\CurrentUserCan;
use WpService\Contracts\GetCurrentScreen;
use WpService\Contracts\GetUsers;
use WpService\Contracts\WpAdminNotice;
use WpService\Contracts\WpCreateNonce;
use WpService\Contracts\WpVerifyNonce;

/**
 * Shows and handles the action for creating a missing organization administrator user.
 */
class MissingOrganizationAdminNotice implements Hookable
{
    private const ACTION = 'create_missing_organization_admin_user';

    private const NONCE_ACTION = 'event_manager_create_missing_organization_admin_user';

    private const NONCE_FIELD = '_event_manager_nonce';

    private ?array $deferredNotice = null;

    /**
    * @param AddAction&AddQueryArg&GetCurrentScreen&CurrentUserCan&WpAdminNotice&WpCreateNonce&WpVerifyNonce&GetUsers&__ $wpService WordPress service abstractions.
     * @param GetField $acfService ACF service abstraction.
     * @param CreateOrganizationAdminUser $createOrganizationAdminUser Shared organization admin creator.
     * @param string $taxonomy The organization taxonomy name.
     */
    public function __construct(
        private AddAction&AddQueryArg&GetCurrentScreen&CurrentUserCan&WpAdminNotice&WpCreateNonce&WpVerifyNonce&GetUsers&__ $wpService,
        private GetField $acfService,
        private CreateOrganizationAdminUser $createOrganizationAdminUser,
        private string $taxonomy
    ) {
    }

    /**
     * Registers the admin hooks.
     */
    public function addHooks(): void
    {
        $this->wpService->addAction('admin_init', [$this, 'handleCreateRequest']);
        $this->wpService->addAction('admin_notices', [$this, 'renderNotice']);
    }

    /**
     * Handles submitted requests to create a missing organization administrator user.
     */
    public function handleCreateRequest(): void
    {
        if (!$this->isCreateRequest()) {
            return;
        }

        $termId = $this->getPostedTermId();
        if ($termId === 0 || !$this->isPostedTaxonomyMatching() || !$this->userCanEditOrganization($termId)) {
            return;
        }

        $nonce = $this->getRequestNonce();
        if ($this->wpService->wpVerifyNonce($nonce, self::NONCE_ACTION) === false) {
            return;
        }

        if ($this->organizationHasAdministrator($termId)) {
            return;
        }

        $email = $this->getOrganizationEmail($termId);
        if ($email === '') {
            return;
        }

        try {
            $this->createOrganizationAdminUser->create($termId, $email);
            $this->deferredNotice = [
                'message' => $this->wpService->__('Organization administrator user created.', 'api-event-manager'),
                'args'    => ['type' => 'success'],
            ];
        } catch (\Exception $exception) {
            $this->deferredNotice = [
                'message' => $this->wpService->__('Unable to create organization administrator user.', 'api-event-manager'),
                'args'    => ['type' => 'error'],
            ];
        }
    }

    /**
     * Renders the appropriate admin notice on the organization term edit screen.
     */
    public function renderNotice(): void
    {
        $termId = $this->getCurrentTermId();
        if ($termId === 0 || !$this->isOrganizationEditScreen() || !$this->userCanEditOrganization($termId)) {
            return;
        }

        if ($this->deferredNotice !== null) {
            $this->wpService->wpAdminNotice($this->deferredNotice['message'], $this->deferredNotice['args']);
            return;
        }

        if ($this->organizationHasAdministrator($termId)) {
            return;
        }

        $email = $this->getOrganizationEmail($termId);
        if ($email === '') {
            $this->wpService->wpAdminNotice(
                $this->wpService->__('Add an email address to this organization before creating an administrator user.', 'api-event-manager'),
                ['type' => 'warning']
            );

            return;
        }

        $this->wpService->wpAdminNotice($this->getCreateActionMarkup($termId, $email), [
            'type'           => 'info',
            'paragraph_wrap' => false,
        ]);
    }

    /**
     * Determines whether the current request targets the create action.
     */
    private function isCreateRequest(): bool
    {
        return $this->getRequestValue('event_manager_action') === self::ACTION;
    }

    /**
     * Determines whether the current admin screen is the organization term edit screen.
     */
    private function isOrganizationEditScreen(): bool
    {
        $screen = $this->wpService->getCurrentScreen();

        return $screen !== null
            && $screen->base === 'term'
            && $screen->taxonomy === $this->taxonomy;
    }

    /**
     * Returns the term ID from the current screen request.
     */
    private function getCurrentTermId(): int
    {
        return isset($_GET['tag_ID']) ? (int) $_GET['tag_ID'] : 0;
    }

    /**
     * Returns the posted term ID from the create request.
     */
    private function getPostedTermId(): int
    {
        return (int) $this->getRequestValue('tag_ID', 0);
    }

    /**
     * Determines whether the posted taxonomy matches the configured organization taxonomy.
     */
    private function isPostedTaxonomyMatching(): bool
    {
        return $this->getRequestValue('taxonomy') === $this->taxonomy;
    }

    /**
     * Determines whether the current user can edit the organization term.
     */
    private function userCanEditOrganization(int $termId): bool
    {
        return $this->wpService->currentUserCan('edit_' . $this->taxonomy . 's', $termId);
    }

    /**
     * Determines whether the organization already has a connected administrator user.
     */
    private function organizationHasAdministrator(int $termId): bool
    {
        $users = $this->wpService->getUsers([
            'role'       => 'organization_administrator',
            'meta_query' => [[
                'key'     => 'organizations',
                'value'   => '"' . $termId . '"',
                'compare' => 'LIKE',
            ]],
        ]);

        return !empty($users);
    }

    /**
     * Returns the organization email stored on the term.
     */
    private function getOrganizationEmail(int $termId): string
    {
        return trim((string) $this->acfService->getField('email', $this->taxonomy . '_' . $termId));
    }

    /**
     * Builds the action notice markup.
     */
    private function getCreateActionMarkup(int $termId, string $email): string
    {
        $escapedEmail = htmlspecialchars($email, ENT_QUOTES, 'UTF-8');
        $escapedUrl   = htmlspecialchars($this->getCreateActionUrl($termId), ENT_QUOTES, 'UTF-8');

        return sprintf(
            '<p>%s %s</p><p><a class="button button-primary" href="%s">%s</a></p>',
            $this->wpService->__('No organization administrator is connected to this organization.', 'api-event-manager'),
            sprintf(
                $this->wpService->__('Create organization administrator for %s.', 'api-event-manager'),
                $escapedEmail
            ),
            $escapedUrl,
            $this->wpService->__('Create organization administrator', 'api-event-manager')
        );
    }

    /**
     * Builds the action URL used by the notice button.
     */
    private function getCreateActionUrl(int $termId): string
    {
        return $this->wpService->addQueryArg([
            'event_manager_action' => self::ACTION,
            'tag_ID'               => $termId,
            'taxonomy'             => $this->taxonomy,
            self::NONCE_FIELD      => $this->wpService->wpCreateNonce(self::NONCE_ACTION),
        ]);
    }

    /**
     * Returns a request value from POST first and then GET.
     */
    private function getRequestValue(string $key, mixed $default = ''): mixed
    {
        if (isset($_POST[$key])) {
            return $_POST[$key];
        }

        if (isset($_GET[$key])) {
            return $_GET[$key];
        }

        return $default;
    }

    /**
     * Returns the request nonce.
     */
    private function getRequestNonce(): string
    {
        return (string) $this->getRequestValue(self::NONCE_FIELD);
    }
}
