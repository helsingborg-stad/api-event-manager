<?php

namespace EventManager\PostToSchema\PostToEventSchema\Commands;

use EventManager\Services\WPService\GetPostTerms;
use Spatie\SchemaOrg\BaseType;

class SetKeywordsCommand implements CommandInterface
{
    public function __construct(
        private BaseType $schema,
        private int $postId,
        private GetPostTerms $wpService
    ) {
    }

    public function execute(): void
    {
        $keywordTerms = $this->wpService->getPostTerms($this->postId, 'keyword', []);

        if (is_array($keywordTerms) && !empty($keywordTerms)) {
            $terms = array_map(fn ($term) => $term->name, $keywordTerms);
            $this->schema->keywords($terms);
        }
    }
}
