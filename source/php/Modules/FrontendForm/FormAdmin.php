<?php

namespace EventManager\Modules\FrontendForm;

use EventManager\HooksRegistrar\Hookable;
use WpService\Contracts\AddFilter;
use WpService\Contracts\GetPostTypeObject;
use WpService\Contracts\GetPostType;
use AcfService\Contracts\GetFieldGroups;

class FormAdmin implements Hookable
{
    public function __construct(
        private AddFilter&GetPostTypeObject&GetPostType $wpService,
        private GetFieldGroups $acfService,
        private string $fieldKey
    ) {
    }

  /**
   * Add hooks to the WordPress system.
   *
   * This method adds hooks to the WordPress system.
   *
   * @return void
   */
    public function addHooks(): void
    {
        $this->wpService->addFilter('acf/load_field/name=' . $this->fieldKey, [$this, 'addOptionsToGroupSelect']);
    }

    public function addOptionsToGroupSelect($field): array
    {
        if ($this->isInEditMode()) {
            return $field;
        }

        $field['choices'] = [];

        $groups = $this->getFilteredGroups();

        if (!empty($groups)) {
            $field['choices'] = $this->generateChoicesFromGroups($groups);
            asort($field['choices']);
        }

        return $field;
    }

    private function getFilteredGroups(): array
    {
        // Get all field groups and filter out those connected to a post type
        $groups = $this->acfService->getFieldGroups();
        return array_filter($groups, function ($item) {
            return isset($item['location'][0][0]['param']) && $item['location'][0][0]['param'] === 'post_type';
        });
    }

    private function generateChoicesFromGroups(array $groups): array
    {
        $choices = [];

        foreach ($groups as $group) {
            $groupTitle             = $group['title'] ?? 'Unnamed Group';
            $postType               = $group['location'][0][0]['value'] ?? '';
            $choices[$group['key']] = $this->getPostTypeLabel($groupTitle, $postType);
        }

        return $choices;
    }

    private function getPostTypeLabel(string $name, string $postTypeName): string
    {
        $postTypeObject = $this->wpService->getPostTypeObject($postTypeName);
        return (!empty($postTypeObject->label) ? "{$postTypeObject->label}: " : "") . $name;
    }

  /**
   * Check if the acf-field confuguration is currently being edited.
   * @return bool
   */
    private function isInEditMode(): bool
    {
        global $post;
        if (is_a($post, 'WP_Post') && in_array($this->wpService->getPostType($post), array('acf-field', 'acf-field-group'))) {
            return true;
        }
        return false;
    }
}
