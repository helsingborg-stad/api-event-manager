<?php
/** @noinspection PhpMissingFieldTypeInspection */
/** @noinspection PhpFullyQualifiedNameUsageInspection */
/** @noinspection PhpUndefinedClassInspection */
/** @noinspection PhpUndefinedNamespaceInspection */

namespace EventManager\Modules\FrontendForm;

//use EventManager\Helper\CacheBust;

/**
 * @property string $description
 * @property string $namePlural
 * @property string $nameSingular
 * @property int $ID
 */
class FrontendForm extends \Modularity\Module
{
    public $slug = 'event-form';
    public $supports = [];

    public function init(): void
    {
        $this->nameSingular = __('Event Form', );
        $this->namePlural   = __('Event Forms', 'api-event-manager');
        $this->description  = __('Module for creating public event form', 'api-event-manager');
        //$this->cacheTtl     = 60;

        add_filter('acf/load_field/name=display_field_groups', [$this, 'filterFieldOptions']);
    }

    public function data(): array
    {
      $fields = $this->getFields();

      // Filter out the keys from the field groups
      $displayFieldGroupsKeys = array_map(function($field) {
        return $field['value'];
      }, $fields['display_field_groups'] ?? []);

      // Return the data
      return [
        'formStart' => function() {
          
        },
        'form' => function() use ($displayFieldGroupsKeys) {
          acf_form([
            'post_id' => 'new_post',
            'post_title' => true,
            'post_content' => true,
            'field_groups' => $displayFieldGroupsKeys,
            'new_post' => [
              'post_type' => 'event',
              'post_status' => 'draft'
            ],
            'submit_value' => __('Create Event', 'api-event-manager')
          ]);
        },
        'formEnd' => function() {
          
        },
      ]; 
    }

    public function template(): string
    {
      return 'frontend-form.blade.php';
    }

    public function script(): void
    {
      acf_form_head();
      /*wp_enqueue_script(
          'frontend-form',
          EVENT_MANAGER_URL . '/dist/'. CacheBust::name('js/assignment-form.js')
      );*/ 
    }

    public function style(): void {
        /*wp_enqueue_style(
            'frontend-form',
            EVENT_MANAGER_URL . '/dist/'. CacheBust::name('js/assignment-form.css')
        );*/ 
    }

    /**
     * Filter the field options.
     *
     * @param array $field The field options.
     * @return array The filtered field options.
     */
    public function filterFieldOptions($field): array
    {
      $field['choices'] = array();

      $selectableFieldGroups = $this->getSelectableFieldGroups();
      if(is_countable($selectableFieldGroups)) {
        foreach($selectableFieldGroups as $group) {
          $field['choices'][$group['value']] = $group['label'];
        }
      }

      return $field;
    }

    /**
     * Retrieves an array of selectable field groups for the 'event' post type.
     *
     * @return array The array of selectable field groups, where each group is represented by an associative array with 'label' and 'value' keys.
     */
    private function getSelectableFieldGroups(): array
    {
      $fieldGroups = acf_get_field_groups(array('post_type' => 'event'));

      $fieldGroups = array_map(function($fieldGroup) {
        return [
          'label' => $fieldGroup['title'],
          'value' => $fieldGroup['key']
        ];
      }, $fieldGroups);

      return $fieldGroups;
    }
}