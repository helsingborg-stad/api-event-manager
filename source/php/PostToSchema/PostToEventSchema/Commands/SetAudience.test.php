<?php

namespace EventManager\PostToSchema\PostToEventSchema\Commands;

use EventManager\Services\WPService\GetTerm;
use Mockery;
use PHPUnit\Framework\TestCase;
use WP_Term;
use WP_Error;

class SetAudienceTest extends TestCase
{
    /**
     * @testdox sets audience from occasions
     */
    public function testExecute()
    {
        $term          = Mockery::mock(WP_Term::class);
        $term->term_id = 1;
        $term->name    = 'Test Audience';
        $meta          = ['audience' => 1];
        $schema        = new \Spatie\SchemaOrg\Thing();
        $wpService     = $this->getWpService($term);

        $command = new SetAudience($schema, $meta, $wpService);
        $command->execute();

        $this->assertEquals('Test Audience', $schema->toArray()['audience']['name']);
        $this->assertEquals(1, $schema->toArray()['audience']['@id']);
    }

    private function getWpService(WP_Term $term): GetTerm
    {
        return new class ($term) implements GetTerm {
            public function __construct(private WP_Term $term)
            {
            }

            public function getTerm(
                int|object $term,
                string $taxonomy = '',
                string $output = OBJECT,
                string $filter = 'raw'
            ): WP_Term|array|WP_Error|null {
                return $this->term;
            }
        };
    }
}
