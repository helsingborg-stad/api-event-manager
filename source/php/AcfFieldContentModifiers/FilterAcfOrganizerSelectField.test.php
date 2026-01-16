<?php

namespace EventManager\AcfFieldContentModifiers;

use AcfService\Contracts\GetField;
use PHPUnit\Framework\TestCase;
use WP_User;
use WpService\Contracts\AddFilter;
use WpService\Contracts\WpGetCurrentUser;

class FilterAcfOrganizerSelectFieldTest extends TestCase
{
    /**
    * @testdox modifyFieldOptions() returns field with choices set to user's organizations if user has organizations
    */
    public function testmodifyFieldOptionsReturnsFieldWithChoicesSetToUserOrganizationsIfUserHasOrganizations()
    {
        // Arrange
        $fieldKey   = 'field_65a4f6af50302';
        $wpService  = $this->getFakeWpService();
        $acfService = $this->getFakeAcfService([ 'getField' => ['organizations' => ['user_1' => [1]]] ]);
        $sut        = new FilterAcfOrganizerSelectField($fieldKey, $wpService, $acfService);

        // Act
        $field = $sut->modifyFieldOptions([], ['key' => $fieldKey]);

        // Assert
        $this->assertEquals(['include' => [1]], $field);
    }

    /**
    * @testdox modifyFieldOptions() returns field with choices set to all terms if user does not belong to organizations
     */
    public function testmodifyFieldOptionsReturnsFieldWithChoicesSetToAllTermsIfUserDoesNotBelongToOrganizations()
    {
        // Arrange
        $fieldKey   = 'field_65a4f6af50302';
        $wpService  = $this->getFakeWpService();
        $acfService = $this->getFakeAcfService([ 'getField' => ['organizations' => ['user_1' => [1]]] ]);
        $sut        = new FilterAcfOrganizerSelectField($fieldKey, $wpService, $acfService);

        // Act
        $args = $sut->modifyFieldOptions([], ['key' => $fieldKey]);

        // Assert
        $this->assertEquals(['include' => [1]], $args);
    }

    private function getFakeWpService(array $db = []): WpGetCurrentUser&AddFilter
    {
        $currentUser     = $this->createMock(WP_User::class);
        $currentUser->ID = 1;

        return new class ($currentUser, $db) implements WpGetCurrentUser, AddFilter {
            public array $getTermsCalls = [];

            public function __construct(private WP_User $currentUser, private array $db)
            {
            }

            public function wpGetCurrentUser(): WP_User
            {
                return $this->currentUser;
            }

            public function addFilter(string $hookName, callable $callback, int $priority = 10, int $acceptedArgs = 1): true
            {
                return true;
            }
        };
    }

    private function getFakeAcfService(array $db = []): GetField
    {
        return new class ($db) implements GetField {
            public function __construct(private array $db)
            {
            }

            public function getField(
                string $selector,
                int|false|string $postId = false,
                bool $formatValue = true,
                bool $escapeHtml = false
            ) {
                return $this->db['getField'][$selector][$postId] ?? null;
            }
        };
    }
}
