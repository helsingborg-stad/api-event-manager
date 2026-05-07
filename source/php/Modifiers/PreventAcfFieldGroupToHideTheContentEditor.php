<?php

namespace EventManager\Modifiers;

use EventManager\HooksRegistrar\Hookable;
use WpService\Contracts\AddFilter;
use WpService\Contracts\GetPostType;
use WpService\Contracts\IsAdmin;

class PreventAcfFieldGroupToHideTheContentEditor implements Hookable
{
    public function __construct(private string $postType, private AddFilter&IsAdmin&GetPostType $wpService)
    {
    }
    public function addHooks(): void
    {
        $this->wpService->addFilter('acf/load_field_group', [$this, 'preventAcfFieldGroupToHideTheContentEditor']);
    }

    public function preventAcfFieldGroupToHideTheContentEditor(array $fieldGroup): array
    {
        // if is edit screen of post type event
        if (!$this->shouldRemoveHideOnScreen($fieldGroup)) {
            return $fieldGroup;
        }

        return $this->removeTheContentFromHideOnScreen($fieldGroup);
    }

    private function removeTheContentFromHideOnScreen(array $fieldGroup): array
    {
        $hideOnScreen = array_filter($fieldGroup['hide_on_screen'], fn ($value) => $value !== 'the_content');

        return [...$fieldGroup, 'hide_on_screen' => $hideOnScreen];
    }

    private function shouldRemoveHideOnScreen(array $fieldGroup): bool
    {
        if (!$this->isEventEditScreen()) {
            return false;
        }

        if (!isset($fieldGroup['hide_on_screen']) || !is_array($fieldGroup['hide_on_screen'])) {
            return false;
        }

        return in_array('the_content', $fieldGroup['hide_on_screen'], true);
    }

    private function isEventEditScreen(): bool
    {
        if (!$this->wpService->isAdmin() || !isset($_GET['post'])) {
            return false;
        }

        return $this->wpService->getPostType($_GET['post']) === $this->postType;
    }
}
