<?php

namespace EventManager\AcfFieldContentModifiers;

use AcfService\Contracts\GetField;
use WpService\Contracts\GetCurrentUser;
use WpService\Contracts\GetTerms;
use PHPUnit\Framework\TestCase;
use WP_Error;
use WP_User;

class FilterAcfOrganizerSelectFieldTest extends TestCase
{
    /**
    * @testdox modifyFieldContent() returns field with choices set to user's organizations if user has organizations
    */
    public function testModifyFieldContentReturnsFieldWithChoicesSetToUserOrganizationsIfUserHasOrganizations()
    {
        // Arrange
        $wpServiceDb = [ 'getTerms' => ['organization' => [1 => [1 => 'The users organization']]] ];
        $wpService   = $this->getFakeWpService($wpServiceDb);
        $acfService  = $this->getFakeAcfService([ 'getField' => ['organizations' => ['user_1' => [1]]] ]);
        $sut         = new FilterAcfOrganizerSelectField('field_65a4f6af50302', $wpService, $acfService);

        // Act
        $field = $sut->modifyFieldContent([]);

        // Assert
        $this->assertEquals(['choices' => [1 => 'The users organization']], $field);
    }

    /**
    * @testdox modifyFieldContent() returns field with choices set to all terms if user does not belong to organizations
     */
    public function testModifyFieldContentReturnsFieldWithChoicesSetToAllTermsIfUserDoesNotBelongToOrganizations()
    {
        // Arrange
        $wpService  = $this->getFakeWpService([ 'getTerms' => ['organization' => [1 => 'Organization 1']] ]);
        $acfService = $this->getFakeAcfService([ 'getField' => ['organizations' => ['user_1' => []]] ]);
        $sut        = new FilterAcfOrganizerSelectField('field_65a4f6af50302', $wpService, $acfService);

        // Act
        $field = $sut->modifyFieldContent([]);

        // Assert
        $this->assertEquals(['choices' => [1 => 'Organization 1']], $field);
    }

    /**
     * @testdox modifyFieldContent() calls getTerms with fields set to id=>name
     */
    public function testModifyFieldContentCallsGetTermsWithFieldsSetToIdName()
    {
        // Arrange
        $wpService  = $this->getFakeWpService([ 'getTerms' => []]);
        $acfService = $this->getFakeAcfService([ 'getField' => []]);
        $sut        = new FilterAcfOrganizerSelectField('field_65a4f6af50302', $wpService, $acfService);

        // Act
        $sut->modifyFieldContent([]);

        // Assert
        $this->assertEquals('id=>name', $wpService->getTermsCalls[0]['fields']);
    }

    private function getFakeWpService(array $db = []): GetTerms&GetCurrentUser
    {
        $currentUser     = $this->getMockBuilder('WP_User')->getMock();
        $currentUser->ID = 1;

        return new class ($currentUser, $db) implements GetTerms, GetCurrentUser {
            public array $getTermsCalls = [];

            public function __construct(private WP_User $currentUser, private array $db)
            {
            }

            public function getTerms(array|string $args = array(), array|string $deprecated = ""): array|string|WP_Error
            {
                $this->getTermsCalls[] = $args;
                if (!empty($args['include'])) {
                    return $this->db['getTerms'][$args['taxonomy']][$args['include'][0]] ?? [];
                }

                return $this->db['getTerms'][$args['taxonomy']] ?? [];
            }

            public function getCurrentUser(): WP_User
            {
                return $this->currentUser;
            }
        };
    }

    private function getFakeAcfService(array $db = []): GetField
    {
        return new class ($db) implements GetField {
            public function __construct(private array $db)
            {
            }

            public function getField(string $selector, int|false|string $postId = false, bool $formatValue = true, bool $escapeHtml = false)
            {
                return $this->db['getField'][$selector][$postId] ?? null;
            }
        };
    }
}
