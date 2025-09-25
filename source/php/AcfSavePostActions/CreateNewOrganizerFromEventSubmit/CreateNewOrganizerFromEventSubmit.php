<?php

namespace EventManager\AcfSavePostActions\CreateNewOrganizerFromEventSubmit;

use AcfService\Contracts\GetFields;
use EventManager\AcfSavePostActions\IAcfSavePostAction;
use WpService\Contracts\WpSetObjectTerms;

class CreateNewOrganizerFromEventSubmit implements IAcfSavePostAction
{
    public function __construct(
        private WpSetObjectTerms $wpService,
        private GetFields $acfService,
        private string $taxonomy,
        private ClearFieldsFromPost\IClearFieldsFromPost $clearFieldsFromPost,
        private CreateNewOrganizationTerm\ICreateNewOrganizationTerm $createNewOrganizationTerm,
        private OrganizerData\ICreateOrganizerDataFromSubmittedFields $organizerDataFactory
    ) {
    }

    public function savePost(int|string $postId): void
    {
        $organizerData = $this->organizerDataFactory->tryCreate($this->acfService->getFields($postId));

        if (!is_null($organizerData)) {
            // Create the term
            $termId = $this->createNewOrganizationTerm->createTerm($organizerData);
            // Clear the fields from the post
            $this->clearFieldsFromPost->clearFields($postId);
            // Assign the term to the post
            $this->wpService->wpSetObjectTerms($postId, $termId, $this->taxonomy);
        }
    }
}
