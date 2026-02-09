<?php

namespace EventManager\SetPostMunicipalityTermsFromPostLocation;

use AcfService\Contracts\GetField;
use EventManager\HooksRegistrar\Hookable;
use WpService\Contracts\AddAction;
use WpService\Contracts\WpSetPostTerms;

class SetPostMunicipalityTermsFromPostLocation implements Hookable
{
    public function __construct(private AddAction&WpSetPostTerms $wpService, private GetField $acfService)
    {
    }

    public function addHooks(): void
    {
        $this->wpService->addAction('post_updated', [$this, 'postUpdated']);
    }

    public function postUpdated(int $postId): void
    {
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
}
