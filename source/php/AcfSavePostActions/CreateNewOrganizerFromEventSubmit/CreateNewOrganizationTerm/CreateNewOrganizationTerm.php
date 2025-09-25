<?php

namespace EventManager\AcfSavePostActions\CreateNewOrganizerFromEventSubmit\CreateNewOrganizationTerm;

use AcfService\Contracts\UpdateField;
use EventManager\AcfSavePostActions\CreateNewOrganizerFromEventSubmit\OrganizerData\IOrganizerData;
use WpService\Contracts\WpInsertTerm;

class CreateNewOrganizationTerm implements ICreateNewOrganizationTerm
{
    public function __construct(
        private string $taxonomy,
        private WpInsertTerm $wpService,
        private UpdateField $acfService
    ) {
    }

    public function createTerm(IOrganizerData $organizerData): int
    {
        $term = $this->wpService->wpInsertTerm($organizerData->getName(), $this->taxonomy);

        if (is_a($term, \WP_Error::class)) {
            if ($term->get_error_code() === 'term_exists') {
                return $term->get_error_data();
            }

            throw new \RuntimeException($term->get_error_message());
        }

        $identifier = $this->taxonomy . '_' . $term['term_id'];

        $this->acfService->updateField('email', $organizerData->getEmail(), $identifier);
        $this->acfService->updateField('contact', $organizerData->getContact(), $identifier);
        $this->acfService->updateField('telephone', $organizerData->getTelephone(), $identifier);
        $this->acfService->updateField('address', $organizerData->getAddress(), $identifier);
        $this->acfService->updateField('url', $organizerData->getUrl(), $identifier);

        return $term['term_id'];
    }
}
