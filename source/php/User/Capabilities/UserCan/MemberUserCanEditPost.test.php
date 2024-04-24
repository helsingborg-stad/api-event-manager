<?php

namespace EventManager\User\Capabilities\UserCan;

use EventManager\Services\AcfService\Functions\GetField;
use WpService\Contracts\GetPostTerms;
use PHPUnit\Framework\TestCase;
use WP_Error;

class MemberUserCanEditPostTest extends TestCase
{
    public function testReturnsTrueIfPostBelongsToSameOrganizationTermAsUser(): void
    {
        $organizationTermId = 1;
        $userId             = 2;
        $postId             = 3;

        $wpService = $this->getWPService([
            'getPostTerms' => [
                'organization' => [
                    $postId => [ (object) ['term_id' => $organizationTermId] ]
                ]
            ]
        ]);

        $acfService = $this->getAcfService(
            [
            'getField' => [
                "user_{$userId}" => [
                        'organizations' => [$organizationTermId]
                    ]
                ]
            ]
        );

        $userCan = new MemberUserCanEditPost($wpService, $acfService);

        $this->assertTrue($userCan->userCan($userId, [$postId]));
    }

    private function getWPService($args): GetPostTerms
    {
        return new class ($args) implements GetPostTerms {
            public function __construct(private array $args)
            {
            }
            public function getPostTerms(
                int $post_id,
                string|array $taxonomy = 'post_tag',
                array $args = array()
            ): array|WP_Error {
                return $this->args['getPostTerms'][$taxonomy][$post_id] ?? [];
            }
        };
    }

    private function getAcfService($args): GetField
    {
        return new class ($args) implements GetField {
            public function __construct(private array $args)
            {
            }
            public function getField(
                string $selector,
                int|false|string $postId = false,
                bool $formatValue = true,
                bool $escapeHtml = false
            ) {
                return $this->args['getField'][$postId][$selector] ?? null;
            }
        };
    }
}
