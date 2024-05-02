<?php

namespace EventManager\Settings;

use AcfService\Contracts\AddOptionsPage;
use WpService\Contracts\AddAction;
use PHPUnit\Framework\TestCase;

class AdminSettingsPageTest extends TestCase
{
    /**
     * @testdox registerSettingsPage() calls AddOptionsPage::addOptionsPage() with expected arguments.
     */
    public function testMenuSlug(): void
    {
        $acfService        = $this->getAcfService();
        $adminSettingsPage = new AdminSettingsPage($this->getWPService(), $acfService);

        $adminSettingsPage->registerSettingsPage();

        $this->assertCount(1, $acfService->registeredOptionsPages);
        $this->assertEquals('event-manager-settings', $acfService->registeredOptionsPages[0]['menu_slug']);
        $this->assertEquals('administrator', $acfService->registeredOptionsPages[0]['capability']);
    }

    private function getWPService(): AddAction
    {
        return new class implements AddAction {
            public function addAction(
                string $tag,
                callable $function_to_add,
                int $priority = 10,
                int $accepted_args = 1
            ): bool {
                return true;
            }
        };
    }

    private function getAcfService(): AddOptionsPage
    {
        return new class implements AddOptionsPage {
            public array $registeredOptionsPages = [];
            public function addOptionsPage(array $options): void
            {
                $this->registeredOptionsPages[] = $options;
            }
        };
    }
}
