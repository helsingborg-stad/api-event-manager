<?php

namespace EventManager\AcfSavePostActions\CreateNewOrganizerFromEventSubmit;

use AcfService\AcfService;
use EventManager\AcfSavePostActions\IAcfSavePostAction;
use WpService\WpService;

class CreateNewOrganizerFromEventSubmit implements IAcfSavePostAction
{
    public function __construct(
        private WpService $wpService,
        private AcfService $acfService,
        private string $taxonomy,
        private ?ClearFieldsFromPost\IClearFieldsFromPost $clearFieldsFromPost = null,
        private ?CreateNewOrganizationTerm\ICreateNewOrganizationTerm $createNewOrganizationTerm = null,
        private ?OrganizerData\ICreateOrganizerDataFromSubmittedFields $organizerDataFactory = null
    ) {
        if (is_null($this->organizerDataFactory)) {
            $this->organizerDataFactory = new OrganizerData\CreateOrganizerDataFromSubmittedFields($this->wpService);
        }

        if (is_null($this->createNewOrganizationTerm)) {
            $this->createNewOrganizationTerm = new CreateNewOrganizationTerm\CreateNewOrganizationTerm(
                $this->taxonomy,
                $this->wpService,
                $this->acfService
            );
        }

        if (is_null($this->clearFieldsFromPost)) {
            $this->clearFieldsFromPost = new ClearFieldsFromPost\ClearFieldsFromPost($this->acfService);
        }
    }

    public function savePost(int|string $postId): void
    {
        if (!$this->shouldCreateOrganizer($postId)) {
            return;
        }

        $organizerData = $this->organizerDataFactory->tryCreate($this->acfService->getFields($postId));

        if (is_null($organizerData)) {
            return;
        }

        // Create the term
        $termId = $this->createNewOrganizationTerm->createTerm($organizerData);
        // Clear the fields from the post
        $this->clearFieldsFromPost->clearFields($postId);
        // Assign the term to the post
        $this->wpService->wpSetObjectTerms($postId, $termId, $this->taxonomy);
    }

    private function shouldCreateOrganizer(int|string $postId): bool
    {
        if (!$this->wpService->currentUserCan('edit_post', $postId)) {
            return false;
        }

        if (defined('DOING_AUTOSAVE') && DOING_AUTOSAVE) {
            return false;
        }

        if ($this->wpService->getPostStatus((int)$postId) !== 'publish') {
            return false;
        }

        return true;
    }
}
