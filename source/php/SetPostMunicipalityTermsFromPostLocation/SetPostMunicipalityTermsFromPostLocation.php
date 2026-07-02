<?php

namespace EventManager\SetPostMunicipalityTermsFromPostLocation;

use AcfService\Contracts\GetField;
use EventManager\HooksRegistrar\Hookable;
use WpService\Contracts\AddAction;
use WpService\Contracts\WpSetPostTerms;

class SetPostMunicipalityTermsFromPostLocation implements Hookable
{
    /**
     * Creates the hook handler.
     *
     * @param AddAction&WpSetPostTerms $wpService  WordPress service wrapper.
     * @param GetField                 $acfService ACF service wrapper.
     */
    public function __construct(private AddAction&WpSetPostTerms $wpService, private GetField $acfService)
    {
    }

    /**
     * Registers hooks.
     */
    public function addHooks(): void
    {
        $this->wpService->addAction('post_updated', [$this, 'postUpdated']);
    }

    /**
     * Sets a municipality from the location address when no explicit municipality was submitted.
     *
     * @param int $postId Post identifier.
     */
    public function postUpdated(int $postId): void
    {
        if ($this->hasSubmittedMunicipalityTerms()) {
            return;
        }

        $locationAddress = $this->acfService->getField('locationAddress', $postId);

        if (empty($locationAddress)) {
            return;
        }

        $addressLocality = $locationAddress['address_locality'] ?? null;

        if (!is_string($addressLocality) || strlen(trim($addressLocality)) === 0) {
            return;
        }

        $this->wpService->wpSetPostTerms($postId, $addressLocality, 'municipality');
    }

    /**
     * Returns whether the current request includes an explicit municipality selection.
     */
    private function hasSubmittedMunicipalityTerms(): bool
    {
        if (!isset($_POST['tax_input']) || !is_array($_POST['tax_input'])) {
            return false;
        }

        if (!array_key_exists('municipality', $_POST['tax_input'])) {
            return false;
        }

        $submittedMunicipality = $_POST['tax_input']['municipality'];

        if (is_array($submittedMunicipality)) {
            return count(array_filter($submittedMunicipality, fn (mixed $term): bool => !empty($term))) > 0;
        }

        if (!is_string($submittedMunicipality)) {
            return false;
        }

        return strlen(trim($submittedMunicipality)) > 0;
    }
}
