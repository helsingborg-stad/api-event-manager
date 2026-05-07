<?php

namespace EventManager\Modifiers;

use EventManager\HooksRegistrar\Hookable;
use PHPUnit\Framework\TestCase;
use WP_Post;
use WpService\Contracts\AddFilter;
use WpService\Contracts\GetPostType;
use WpService\Contracts\IsAdmin;

class PreventAcfFieldGroupToHideTheContentEditorTest extends TestCase
{
    /**
     * @testdox can be instantiated
     */
    public function testCanBeInstantiated(): void
    {
        $wpService = static::createWpService();
        $modifier  = new PreventAcfFieldGroupToHideTheContentEditor('event', $wpService);
        $this->assertInstanceOf(PreventAcfFieldGroupToHideTheContentEditor::class, $modifier);
    }

    /**
     * @testdox adds the correct filter hook
     */
    public function testAddsCorrectFilterHook(): void
    {
        $wpService = static::createWpService();
        $modifier  = new PreventAcfFieldGroupToHideTheContentEditor('event', $wpService);
        $modifier->addHooks();

        $this->assertCount(1, $wpService->addedFilters);
        $this->assertSame('acf/load_field_group', $wpService->addedFilters[0]['hookName']);
        $this->assertSame([$modifier, 'preventAcfFieldGroupToHideTheContentEditor'], $wpService->addedFilters[0]['callback']);
    }

    /**
     * @testdox does not modify field group if not on admin screen
     */
    public function testDoesNotModifyFieldGroupIfNotOnAdminScreen(): void
    {
        $wpService = static::createWpService(false);
        $modifier  = new PreventAcfFieldGroupToHideTheContentEditor('event', $wpService);

        $fieldGroup = [
            'hide_on_screen' => ['the_content', 'other_field'],
        ];

        $result = $modifier->preventAcfFieldGroupToHideTheContentEditor($fieldGroup);

        $this->assertSame($fieldGroup, $result);
    }

    /**
     * @testdox does not modify field group if not on event edit screen
     */
    public function testDoesNotModifyFieldGroupIfNotOnEventEditScreen(): void
    {
        $_GET['post'] = 123; // Simulate being on a post edit screen
        $wpService    = static::createWpService(true, 'post');
        $modifier     = new PreventAcfFieldGroupToHideTheContentEditor('event', $wpService);

        $fieldGroup = [
            'hide_on_screen' => ['the_content', 'other_field'],
        ];

        $result = $modifier->preventAcfFieldGroupToHideTheContentEditor($fieldGroup);

        $this->assertSame($fieldGroup, $result);
    }

    /**
     * @testdox removes 'the_content' from hide_on_screen if on event edit screen
     */
    public function testRemovesTheContentFromHideOnScreenIfOnEventEditScreen(): void
    {
        $_GET['post'] = 123; // Simulate being on a post edit screen
        $wpService    = static::createWpService(true, 'event');
        $modifier     = new PreventAcfFieldGroupToHideTheContentEditor('event', $wpService);

        $result = $modifier->preventAcfFieldGroupToHideTheContentEditor([
            'hide_on_screen' => ['the_content', 'other_field'],
        ]);

        $this->assertNotContains('the_content', $result['hide_on_screen']);
    }

    private static function createWpService(bool $isAdmin = true, string $postType = 'event'): AddFilter&IsAdmin&GetPostType
    {
        return new class ($isAdmin, $postType) implements AddFilter, IsAdmin, GetPostType {
            public array $addedFilters = [];

            public function __construct(private bool $isAdmin, private string $postType)
            {
            }

            public function addFilter(string $hookName, callable $callback, int $priority = 10, int $acceptedArgs = 1): true
            {
                $this->addedFilters[] = [
                    'hookName'     => $hookName,
                    'callback'     => $callback,
                    'priority'     => $priority,
                    'acceptedArgs' => $acceptedArgs,
                ];

                return true;
            }

            public function isAdmin(): bool
            {
                return $this->isAdmin;
            }

            public function getPostType(int|WP_Post|null $post = null): string|false
            {
                return $this->postType;
            }
        };
    }
}
