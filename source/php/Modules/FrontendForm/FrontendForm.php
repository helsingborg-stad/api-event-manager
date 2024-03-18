<?php
/** @noinspection PhpMissingFieldTypeInspection */
/** @noinspection PhpFullyQualifiedNameUsageInspection */
/** @noinspection PhpUndefinedClassInspection */
/** @noinspection PhpUndefinedNamespaceInspection */

namespace EventManager\Modules\FrontendForm;

use ComponentLibrary\Init as ComponentLibraryInit;
use EventManager\Services\WPService\EnqueueStyle;
use EventManager\Services\WPService\WPServiceFactory;
use EventManager\Decorators\ManifestFilePathDecorator;

use Throwable;

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

    private EnqueueStyle $wpService;

    private $blade = null; 

    public function init(): void
    {
      $this->nameSingular = __('Event Form', 'api-event-manager');
      $this->namePlural   = __('Event Forms', 'api-event-manager');
      $this->description  = __('Module for creating public event form', 'api-event-manager');

      $manifestFilePathDecorator = new ManifestFilePathDecorator();
      $this->wpService = WPServiceFactory::create(
          $manifestFilePathDecorator
      );
    }

    public function data(): array
    {
      $fields = $this->getFields();

      $htmlUpdatedMessage = $this->renderView('partials.message', [
        'text' => '%s',
        'icon' => ['name' => 'info'],
        'type' => 'warning'
      ]);

      $htmlSubmitButton = $this->renderView('partials.submit', [
        'text' => __('Create Event', 'api-event-manager')
      ]);

      // Return the data
      return [
        'form' => function() use($htmlUpdatedMessage, $htmlSubmitButton) {
          acf_form([
            'post_id' => 'new_post',
            'post_title' => true,
            'post_content' => true,
            'field_groups' => $this->fieldGroups,
            'uploader' => 'basic',
            'updated_message' => __("The event has been submitted for review. You will be notified when the event has been published.", 'acf'),
            'html_updated_message' => $htmlUpdatedMessage,
            'html_submit_button' => $htmlSubmitButton,
            'new_post' => [
              'post_type' => 'event',
              'post_status' => 'draft'
            ],
            'submit_value' => __('Create Event', 'api-event-manager')
          ]);
        }
      ]; 
    }

    public function template(): string
    {
      return 'frontend-form.blade.php';
    }

    public function script(): void
    {
      acf_form_head();
    }

    public function style(): void {
        $this->wpService->enqueueStyle('event-manager-frontend-form');
    }

    /**
     * @param $view
     * @param array $data
     * @return bool
     * @throws \Exception
     * 
     */
    public function renderView($view, $data = array()): string
    {
        if (is_null($this->blade)) {
          $this->blade = (
            new ComponentLibraryInit([])
          )->getEngine();
        }

        try {
            return $this->blade->makeView($view, $data, [], $this->templateDir)->render();
        } catch (Throwable $e) {
            echo '<pre class="c-paper" style="max-height: 400px; overflow: auto;">';
            echo '<h2>Could not find view</h2>'; 
            echo '<strong>' . $e->getMessage() . '</strong>';
            echo '<hr style="background: #000; outline: none; border:none; display: block; height: 1px;"/>';
            echo $e->getTraceAsString();
            echo '</pre>';
        }

        return false;
    }
}
