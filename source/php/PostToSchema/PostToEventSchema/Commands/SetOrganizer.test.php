<?php

namespace EventManager\PostToSchema\PostToEventSchema\Commands;

use EventManager\PostToSchema\PostToEventSchema\Commands\Helpers\CommandHelpers;
use AcfService\Contracts\GetFields;
use WpService\Contracts\GetPostTerms;
use PHPUnit\Framework\TestCase;
use Spatie\SchemaOrg\BaseType;
use WP_Error;

class SetOrganizerTest extends TestCase
{
    /**
     * @testdox sets organizer from post terms
     */
    public function testExecute()
    {
        $event          = new class extends BaseType {
        };
        $postId         = 1;
        $wpService      = $this->getWpService();
        $acfService     = $this->getAcfService();
        $commandHelpers = new CommandHelpers();
        $command        = new SetOrganizer($event, $postId, $wpService, $acfService, $commandHelpers);

        $command->execute();

        $this->assertEquals('Organization Name', $event->toArray()['organizer']['name']);
    }

    private function getWpService(): GetPostTerms
    {
        return new class implements GetPostTerms {
            public function getPostTerms(
                int $post_id,
                string|array $taxonomy = 'post_tag',
                array $args = array()
            ): array|WP_Error {
                return [
                    (object) [
                        'name'     => 'Organization Name',
                        'taxonomy' => 'organization',
                        'term_id'  => 1,
                    ],
                ];
            }
        };
    }

    private function getAcfService(): GetFields
    {
        return new class implements GetFields {
            public function getFields(
                mixed $postId = false,
                bool $formatValue = true,
                bool $escapeHtml = false
            ): array {
                return [
                    'url'       => 'https://example.com',
                    'email'     => '',
                    'telephone' => '',
                    'address'   => [
                        'streetAddress'   => '123 Main St',
                        'addressLocality' => 'Anytown',
                        'addressRegion'   => 'NY',
                        'postalCode'      => '12345',
                        'addressCountry'  => 'US',
                    ],
                ];
            }
        };
    }
}
