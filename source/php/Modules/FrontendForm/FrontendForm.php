<?php

/** @noinspection PhpMissingFieldTypeInspection */

/** @noinspection PhpFullyQualifiedNameUsageInspection */
/** @noinspection PhpUndefinedClassInspection */
/** @noinspection PhpUndefinedNamespaceInspection */

namespace EventManager\Modules\FrontendForm;

use ComponentLibrary\Init as ComponentLibraryInit;
use WpService\Contracts\EnqueueStyle;
use WpService\Contracts\WPServiceFactory;
use EventManager\Resolvers\FileSystem\ManifestFilePathResolver;
use EventManager\Resolvers\FileSystem\StrictFilePathResolver;
use EventManager\Services\FileSystem\FileSystemFactory;
use WpService\Implementations\NativeWpService;
use PharIo\Manifest\Manifest;
use Throwable;

/**
 * @property string $description
 * @property string $namePlural
 * @property string $nameSingular
 * @property int $ID
 */
class FrontendForm extends \Modularity\Module
{
    public $slug     = 'event-form';
    public $supports = [];

    // The field groups that should be displayed in the form.
    private $fieldGroups = [
      'group_65a115157a046'
    ];

    private $blade = null;

    private EnqueueStyle $wpService;

    public function init(): void
    {
        $this->nameSingular = __('Event Form', 'api-event-manager');
        $this->namePlural   = __('Event Forms', 'api-event-manager');
        $this->description  = __('Module for creating public event form', 'api-event-manager');

        $this->wpService = new NativeWpService(); // TODO: use custom modularity middleware.
    }

    public function data(): array
    {
        $fields = $this->getFields(); //Needs to be called, otherwise a notice will be thrown.

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
        'form' => function () use ($htmlUpdatedMessage, $htmlSubmitButton) {
            acf_form([
            'post_id'               => 'new_post',
            'post_title'            => true,
            'post_content'          => false,
            'field_groups'          => $this->fieldGroups,
            'form_attributes'       => ['class' => 'acf-form js-form-validation js-form-validation'],
            'uploader'              => 'basic',
            'updated_message'       => __("The event has been submitted for review. You will be notified when the event has been published.", 'acf'),
            'html_updated_message'  => $htmlUpdatedMessage,
            'html_submit_button'    => $htmlSubmitButton,
            'new_post'              => [
              'post_type'   => 'event',
              'post_status' => 'draft'
            ],
            'instruction_placement' => 'field',
            'submit_value'          => __('Create Event', 'api-event-manager')
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

    public function style(): void
    {
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
