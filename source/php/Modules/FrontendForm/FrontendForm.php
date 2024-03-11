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

    // The field groups that should be displayed in the form.
    private $fieldGroups = [
      'group_65a115157a046'
    ];

    public function init(): void
    {
        $this->nameSingular = __('Event Form', );
        $this->namePlural   = __('Event Forms', 'api-event-manager');
        $this->description  = __('Module for creating public event form', 'api-event-manager');
    }

    public function data(): array
    {
      $fields = $this->getFields();

      // Return the data
      return [
        'formStart' => function() {
          
        },
        'form' => function() {
          acf_form([
            'post_id' => 'new_post',
            'post_title' => true,
            'post_content' => true,
            'field_groups' => $this->fieldGroups,
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
}