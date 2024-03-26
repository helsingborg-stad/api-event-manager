<?php

namespace EventManager\PostToSchema\PostToEventSchema\Commands;

use EventManager\Services\WPService\GetTerm;
use Spatie\SchemaOrg\BaseType;

class SetAudience implements CommandInterface
{
    public function __construct(private BaseType $schema, private array $meta, private GetTerm $wpService)
    {
    }

    public function execute(): void
    {
        $audienceId = $this->meta['audience'] ?: null;

        if (!$audienceId) {
            return;
        }

        $audienceTerm = $this->wpService->getTerm($audienceId, 'audience');

        if (!is_a($audienceTerm, \WP_Term::class)) {
            return;
        }

        $audience = new \Spatie\SchemaOrg\Audience();
        $audience->identifier((int)$audienceTerm->term_id);
        $audience->name($audienceTerm->name);

        $this->schema->audience($audience);
    }
}
