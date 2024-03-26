<?php

namespace EventManager\PostToSchema\PostToEventSchema\Commands;

use Spatie\SchemaOrg\BaseType;

class SetUrlCommand implements CommandInterface
{
    public function __construct(private BaseType $schema, private array $meta)
    {
    }

    public function execute(): void
    {
        $occasions = $this->meta['occasions'] ?? [];

        if (empty($occasions) || count($occasions) !== 1) {
            return;
        }

        $occasionsUrl = $occasions[0]['url'] ?: null;

        if ($occasionsUrl && filter_var($occasionsUrl, FILTER_VALIDATE_URL)) {
            $this->schema->url($occasionsUrl);
        }
    }
}
