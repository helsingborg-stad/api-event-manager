<?php

namespace EventManager\User\UserHasCap\Implementations\Helpers;

use AcfService\Contracts\GetField;
use PHPUnit\Framework\TestCase;
use WP_Error;
use WpService\Contracts\GetPostTerms;

class PostBelongsToSameOrganizationAsUserTest extends TestCase
{
    /**
     * @testdox postBelongsToSameOrganizationTermAsUser() returns true if the post belongs to the same organization as the user
     */
    public function testPostBelongsToSameOrganizationTermAsUserReturnsTrueIfThePostBelongsToTheSameOrganizationAsTheUser()
    {
        $userId = 1;
        $postId = 1;

        $postBelongsToSameOrganizationAsUser = new PostBelongsToSameOrganizationAsUser(
            $this->getWpService(),
            $this->getAcfService()
        );

        $this->assertTrue($postBelongsToSameOrganizationAsUser->postBelongsToSameOrganizationTermAsUser($userId, $postId));
    }

    /**
     * @testdox postBelongsToSameOrganizationTermAsUser() returns false if the post does not belong to the same organization as the user
     */
    public function testPostBelongsToSameOrganizationTermAsUserReturnsFalseIfThePostDoesNotBelongToTheSameOrganizationAsTheUser()
    {
        $userId = 2;
        $postId = 1;

        $postBelongsToSameOrganizationAsUser = new PostBelongsToSameOrganizationAsUser(
            $this->getWpService(),
            $this->getAcfService()
        );

        $this->assertFalse($postBelongsToSameOrganizationAsUser->postBelongsToSameOrganizationTermAsUser($userId, $postId));
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
                    1 => ['organization' => [(object) ['term_id' => 1]]],
                ][$post_id][$taxonomy] ?? [];
            }
        };
    }

    private function getAcfService(): GetField
    {
        return new class implements GetField {
            public function getField(string $selector, int|false|string $postId = false, bool $formatValue = true, bool $escapeHtml = false)
            {
                return [
                    'organizations' => ['user_1' => ['1']],
                ][$selector][$postId] ?? false;
            }
        };
    }
}
