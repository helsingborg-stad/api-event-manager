<?php

/** @noinspection PhpMissingFieldTypeInspection */

/** @noinspection PhpFullyQualifiedNameUsageInspection */
/** @noinspection PhpUndefinedClassInspection */
/** @noinspection PhpUndefinedNamespaceInspection */

namespace EventManager\Modules\FrontendForm;

use ComponentLibrary\Init as ComponentLibraryInit;
use EventManager\Services\WPService\EnqueueStyle;
use EventManager\Services\WPService\WPServiceFactory;
use EventManager\Resolvers\FileSystem\ManifestFilePathResolver;
use EventManager\Resolvers\FileSystem\StrictFilePathResolver;
use EventManager\Services\FileSystem\FileSystemFactory;
use EventManager\Services\WPService\Implementations\NativeWpService;
use PharIo\Manifest\Manifest;
use Throwable;
use function acf_get_field_groups;

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
        'group_661e41bb1781f', // Test step 1
        'group_661e425070deb', // Test step 2
        'group_65a115157a046'
    ];

    private $formStepKey = 'step'; // The query parameter for the form steps.

    private $blade = null;

    private EnqueueStyle $wpService;

    public function init(): void
    {
        $this->nameSingular = __('Event Form', 'api-event-manager');
        $this->namePlural   = __('Event Forms', 'api-event-manager');
        $this->description  = __('Module for creating public event form', 'api-event-manager');

        $this->wpService = new NativeWpService(); // TODO: use custom modularity middleware.

        add_filter('query_vars',[$this, 'registerFormStepQueryVar']); // add from wpservice
    }

    public function data(): array
    {
        $fields = $this->getFields(); //Needs to be called, otherwise a notice will be thrown.

        $htmlUpdatedMessage = $this->renderView('partials.message', [
            'text' => '%s',
            'icon' => ['name' => 'info'],
            'type' => 'sucess'
        ]);

        $htmlSubmitButton = $this->renderView('partials.submit', [
            'text' => __('Create Event', 'api-event-manager')
        ]);

        //Collect state data
        $currentStep        = $this->getCurrentFormStep($this->formStepKey); // eg: 1
        $currentStepKey     = $this->getFieldGroup($this->formStepKey); // eg: group_abc123
        $isValidStep        = $this->isValidStep($this->fieldGroups, $currentStep);

        $editMode           = $this->toggleEditMode($currentStep);

        //Show error if step is not valid
        if(!$isValidStep) {
            return [
                'error' => $this->renderView('partials.message', [
                    'text' => __('Whoops! It looks like we ran out of form.', 'api-event-manager'),
                    'icon' => ['name' => 'error'],
                    'type' => 'sucess'
                ])
            ];
        } else {
            $data['error'] = false;
        }

        //Create step objects
        foreach($this->fieldGroups as $fieldGroup) {
            $data['steps'][$fieldGroup] = (object) [
                'title' => $this->getStepTitle($fieldGroup),
                'isCurrent' => ($fieldGroup === $currentStepKey),
                'isPassed' => $this->isStepPassed($fieldGroup, $this->fieldGroups),
            ];
        }

        //Get current step form
        $data['form'] = (function () use ($htmlUpdatedMessage, $htmlSubmitButton, $currentStepKey, $editMode) {
            acf_form([
                'post_id'               => 'new_post',
                'post_title'            => ($editMode === 'update_post'),
                'post_content'          => false,
                'field_groups'          => [
                    $currentStepKey
                ],
                'form_attributes' => ['class' => 'acf-form js-form-validation js-form-validation'],
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
        });

        return $data;
    }

    /**
     * Retrive the step title, based on the step key.
     * If no title is found, the default title will be returned.
     *
     * @param string $stepKey The key of the step.
     * @param string $stepTitle The default title of the step.
     * 
     * @return string The title of the step.
     */
    public function getStepTitle($stepKey, $stepTitle = ''): string {
        $fieldGroups = acf_get_field_groups();
        array_walk($fieldGroups, function($fieldGroup) use ($stepKey, &$stepTitle) {
            if($fieldGroup['key'] === $stepKey) {
                $stepTitle = $fieldGroup['title'];
            }
        });
        return $stepTitle;
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

    /**
     * Registers the form step query variable.
     *
     * This method takes an array of registered query variables and adds the form step key to it.
     *
     * @param array $registeredQueryVars The array of registered query variables.
     * @return array The updated array of registered query variables.
     */
    public function registerFormStepQueryVar(array $registeredQueryVars): array {
        return $registeredQueryVars = array_merge(
            $registeredQueryVars,
            [$this->formStepKey]
        );
    }

    /**
     * Retrieves the field group for a given form step.
     *
     * @param string $stepkey The key of the form step.
     * @return string|bool The field group for the specified form step, or false if not found.
     */
    private function getFieldGroup(string $stepkey): string|bool {
        $formStep = $this->getCurrentFormStep($stepkey);
        $formStep = $formStep - 1;

        if($formStep > count($this->fieldGroups) - 1) {
            return false;
        }

        if(!isset($this->fieldGroups[$formStep])) {
            return false;
        }

        return $this->fieldGroups[$formStep];    
    }

    /**
     * Retrieves the current step of the form based on the provided step key.
     *
     * @param string $stepkey The key used to retrieve the current step.
     * @return int The current step of the form.
     */
    private function getCurrentFormStep(string $stepkey): int {
        $step = get_query_var($stepkey, 1);
        if(is_numeric($step)) {
            return $step;
        }
        return 1;
    }

    /**
     * Toggles the edit mode based on the given step value.
     *
     * @param int $step The step value to determine the edit mode.
     * @return string The edit mode, either 'edit' or 'create'.
     */
    private function toggleEditMode(int $step): string {
        return empty($step) ? 'update_post' : 'new_post';
    }

    private function isLastStep($currentStep, $fieldGroups) {
        return $currentStep === (count($fieldGroups) + 1);
    }

    //Function that checks if this step is passed using array_search
    private function isStepPassed($currentStep, $fieldGroups) {
        return array_search($currentStep, $fieldGroups) !== false;
    }

    public function isCurrentStep($fieldGroup, $currentStepKey){
        return ($fieldGroup === $currentStepKey); 
    }

    public function isValidStep($fieldGroups, $currentStepKey){
        return count($fieldGroups) >= $currentStepKey;
    }
}
