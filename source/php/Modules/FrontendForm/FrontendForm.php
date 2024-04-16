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
    public $hidden   = false;

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

        //TODO: Resolve issue with modularity style/script not loading in drafts.
        add_action('wp_enqueue_scripts', function() {
            $this->wpService->enqueueStyle('event-manager-frontend-form');
        });
        
    }

    public function data(): array
    {
        $fields = $this->getFields(); //Needs to be called, otherwise a notice will be thrown.

        $htmlUpdatedMessage = $this->renderView('partials.message', [
            'text' => '%s',
            'icon' => ['name' => 'info'],
            'type' => 'sucess'
        ]);

        //Collect state data
        $currentStep        = $this->getCurrentFormStep($this->formStepKey); // eg: 1
        $previousStep       = $this->getPreviousFormStep($this->formStepKey);
        $nextStep           = $this->getNextFormStep($this->formStepKey);
        $currentStepKey     = $this->getFieldGroup($this->formStepKey); // eg: group_abc123
        $isValidStep        = $this->isValidStep($this->fieldGroups, $currentStep);
        $isLastStep         = $this->isLastStep($currentStep, $this->fieldGroups);
        $isFirstStep        = $currentStep === 1;
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
                'isPassed' => $this->isStepPassed($currentStep, $fieldGroup, $this->fieldGroups),
            ];
        }

        $htmlSubmitButton = $this->getButtons($isLastStep, $isFirstStep);

        //Get current step form
        $data['form'] = (function () use ($htmlUpdatedMessage, $htmlSubmitButton, $currentStepKey, $currentStep, $editMode) {
            acf_form([
                'post_id'               => 'new_post',
                'return'                => '%post_url%&step=' . ($currentStep + 1),
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
        //$this->wpService->enqueueStyle('event-manager-frontend-form');
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

    private function getButtons($isLastStep, $isFirstStep): string {
        $navItems = [];
        if($isLastStep) {
            $navItems[] = $this->renderView('partials.previous', [
                'text' => __('Previous', 'api-event-manager')
            ]);
            $navItems[] = $this->renderView('partials.submit', [
                'text' => __('Submit Event', 'api-event-manager')
            ]);
        } elseif($isFirstStep) {
            $navItems[] = $this->renderView('partials.next', [
                'text' => __('Next', 'api-event-manager'),
                'classList' => ['u-margin__left--auto']
            ]);
        } else {
            $navItems[] = $this->renderView('partials.previous', [
                'text' => __('Previous', 'api-event-manager')
            ]);
            $navItems[] = $this->renderView('partials.next', [
                'text' => __('Next', 'api-event-manager')
            ]);
        }
        return $this->renderView('partials.button-wrapper', [
            'navItems' => $navItems
        ]);
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
        if(is_numeric($step) && $step > 0) {
            return $step;
        }
        return 1;
    }

    /**
     * Get the previous form step based on the given step key.
     *
     * @param string $stepkey The step key.
     * @return int The previous form step.
     */
    private function getPreviousFormStep(string $stepkey): int
    {
        return $this->getCurrentFormStep($stepkey) - 1;
    }

    /**
     * Get the next form step based on the given step key.
     *
     * @param string $stepkey The step key.
     * @return int The next form step.
     */
    private function getNextFormStep(string $stepkey): int
    {
        return $this->getCurrentFormStep($stepkey) + 1;
    }

    /**
     * Toggles the edit mode based on the given step value.
     *
     * @param int $step The step value to determine the edit mode.
     * @return string The edit mode, either 'edit' or 'create'.
     */
    private function toggleEditMode(int $step): string
    {
        return empty($step) ? 'update_post' : 'new_post';
    }
    
    /**
     * Checks if the current step is the last step in the form.
     *
     * @param int $currentStep The current step in the form.
     * @param array $fieldGroups An array of field groups in the form.
     * @return bool Returns true if the current step is the last step, false otherwise.
     */
    private function isLastStep($currentStep, $fieldGroups): bool
    {
        return $currentStep === count($fieldGroups);
    }

    /**
     * Checks if a step is passed based on the current step, current step key, and field groups.
     *
     * @param int $currentStep The current step number.
     * @param string $currentStepKey The step key to check.
     * @param array $fieldGroups The array of field groups.
     * @return bool Returns true if the step is passed, false otherwise.
     */
    private function isStepPassed($currentStep, $currentStepKey, $fieldGroups): bool
    {
        if($currentStep <= array_search($currentStepKey, $fieldGroups)) {
            return true;
        }
        return false;
    }

    /**
     * Checks if the given field group is the current step.
     *
     * @param mixed $fieldGroup The field group to check.
     * @param mixed $currentStepKey The current step key.
     * @return bool Returns true if the field group is the current step, false otherwise.
     */
    public function isCurrentStep($fieldGroup, $currentStepKey): bool 
    {
        return ($fieldGroup === $currentStepKey); 
    }

    /**
     * Checks if the current step is valid based on the given field groups and current step key.
     *
     * @param array $fieldGroups An array of field groups.
     * @param int $currentStepKey The current step key.
     * @return bool Returns true if the current step is valid, false otherwise.
     */
    public function isValidStep($fieldGroups, $currentStepKey): bool
    {
        return count($fieldGroups) >= $currentStepKey;
    }
}
