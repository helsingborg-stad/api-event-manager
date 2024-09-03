<?php

namespace EventManager\Modules\FrontendForm;

use WpService\Contracts\AddFilter;
use WpService\Contracts\GetPostTypeObject;
use WpService\Contracts\GetPostType;
use AcfService\Contracts\GetFieldGroups;

class FormAdmin
{
    public function __construct(
        private AddFilter&GetPostTypeObject&GetPostType $wpService,
        private GetFieldGroups $acfService,
        private string $fieldKey
    ) {
      $this->wpService->addFilter('acf/load_field/name=' . $fieldKey, [$this, 'addOptionsToGroupSelect']);
    }

  /**
   * Adds the field groups to the select field.
   *
   * This method takes a field and adds the field groups to the select field.
   *
   * @param array $field The field to add the field groups to.
   * @return array The updated field.
   */
    public function addOptionsToGroupSelect($field)
    {
        if ($this->isInEditMode() === true) {
            return $field;
        }
    
        $field['choices'] = array();
    
        // Get all field groups, filter out all that are connected to a post type.
        $groups = $this->acfService->getFieldGroups();
        $groups = array_filter($groups, function ($item) {
            return isset($item['location'][0][0]['param']) && $item['location'][0][0]['param'] === 'post_type';
        });
    
        // Define a function to get the post type label.
        $postTypeLabel = function ($name, $postTypeName) {
            $postTypeObject = $this->wpService->getPostTypeObject($postTypeName);
            return (!empty($postTypeObject->label) ? "{$postTypeObject->label}: " : "") . $name;
        }; 
    
        // Add groups to the select field
        if (!empty($groups)) {
            foreach ($groups as $group) {
                $groupTitle = $group['title'] ?? 'Unnamed Group'; // Use a default title if not set
                $postType = $group['location'][0][0]['value'] ?? '';
    
                // Call the closure with the group's title and post type name
                $field['choices'][$group['key']] = $postTypeLabel($groupTitle, $postType);
            }
        }

        //Order by value 
        asort($field['choices']);
    
        return $field;
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
