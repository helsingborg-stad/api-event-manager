<?php

namespace EventManager\AcfSavePostActions\CreateNewOrganizerFromEventSubmit\CreateNewOrganizationTerm;

use AcfService\Contracts\UpdateField;
use EventManager\AcfSavePostActions\CreateNewOrganizerFromEventSubmit\OrganizerData\IOrganizerData;
use WpService\Contracts\EscUrlRaw;
use WpService\Contracts\SanitizeEmail;
use WpService\Contracts\SanitizeTextField;
use WpService\Contracts\WpInsertTerm;

class CreateNewOrganizationTerm implements ICreateNewOrganizationTerm
{
    public function __construct(
        private string $taxonomy,
        private WpInsertTerm&SanitizeTextField&SanitizeEmail&EscUrlRaw $wpService,
        private UpdateField $acfService
    ) {
    }

    public function createTerm(IOrganizerData $organizerData): int
    {
        $name = $this->wpService->sanitizeTextField($organizerData->getName());
        $email = $this->wpService->sanitizeEmail($organizerData->getEmail());
        $contact = $this->wpService->sanitizeTextField($organizerData->getContact());
        $telephone = $this->wpService->sanitizeTextField($organizerData->getTelephone());
        $address = $this->wpService->sanitizeTextField($organizerData->getAddress());
        $url = $this->wpService->escUrlRaw($organizerData->getUrl());
        $term = $this->wpService->wpInsertTerm($name, $this->taxonomy);

        if (is_a($term, \WP_Error::class)) {
            if ($term->get_error_code() === 'term_exists') {
                return $term->get_error_data();
            }

            throw new \RuntimeException($term->get_error_message());
        }

        $identifier = $this->taxonomy . '_' . $term['term_id'];

        $this->acfService->updateField('email', $email, $identifier);
        $this->acfService->updateField('contact', $contact, $identifier);
        $this->acfService->updateField('telephone', $telephone, $identifier);
        $this->acfService->updateField('address', $address, $identifier);
        $this->acfService->updateField('url', $url, $identifier);

        return $term['term_id'];
    }
}
