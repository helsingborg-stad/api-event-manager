<?php

namespace EventManager\User\UserTableFilterForm;

use EventManager\HooksRegistrar\Hookable;
use Override;
use WpService\Contracts\__;
use WpService\Contracts\AddAction;
use WpService\Contracts\DoAction;
use WpService\Contracts\SubmitButton;
use WpService\Implementations\FakeWpService;

class UserTableFilterFormTest extends \PHPUnit\Framework\TestCase
{
    protected function tearDown(): void
    {
        // Reset the global variable after each test to avoid side effects
        $GLOBALS['wp_list_table'] = null;
    }

    /**
     * @testdox does not output the organization filter dropdown when there are no users in the list table
     */
    public function testDoesNotOutputOrganizationFilterDropdownWhenNoUsersInListTable(): void
    {
        $wpService                = new FakeWpService();
        $userTableFilterForm      = new UserTableFilterForm($wpService);
        $GLOBALS['wp_list_table'] = (object) ['items' => []];

        $userTableFilterForm->outputOrganizationFilterDropdown('top');

        $this->expectOutputString('');
    }

    /**
     * @testdox only outputs the organization filter dropdown when there are users in the list table and the $which parameter is 'top'
     */
    public function testDoesNotOutputOrganizationFilterDropdownWhenUsersInListTableAndWhichIsNotTop(): void
    {
        $wpService                = new FakeWpService();
        $userTableFilterForm      = new UserTableFilterForm($wpService);
        $GLOBALS['wp_list_table'] = (object) ['items' => [1, 2, 3]];

        $userTableFilterForm->outputOrganizationFilterDropdown('below');

        $this->expectOutputString('');
    }

    /**
     * @testdox outputs the organization filter dropdown when there are users in the list table and the $which parameter is 'top'
     */
    public function testOutputsOrganizationFilterDropdownWhenUsersInListTableAndWhichIsTop(): void
    {
        $wpService = new class implements AddAction, __, DoAction, SubmitButton {
            public function addAction(string $hookName, callable $callback, int $priority = 10, int $acceptedArgs = 1): true
            {
                return true;
            }

            public function __(string $text, string $domain = 'default'): string
            {
                return $text;
            }

            public function doAction(string $tag, ...$args): void
            {
                echo '<doAction/>';
            }

            public function submitButton(string $text = '', string $type = 'primary', string $name = 'submit', bool $wrap = true, array|string $otherAttributes = ''): void
            {
                echo '<submitButton/>';
            }
        };

        $userTableFilterForm      = new UserTableFilterForm($wpService);
        $GLOBALS['wp_list_table'] = (object) ['items' => [1, 2, 3]];

        $userTableFilterForm->outputOrganizationFilterDropdown('top');

        $this->expectOutputString('<div class="alignleft actions"><doAction/><submitButton/></div>');
    }
}
