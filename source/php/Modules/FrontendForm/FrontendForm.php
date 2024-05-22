<?php

/** @noinspection PhpMissingFieldTypeInspection */

/** @noinspection PhpFullyQualifiedNameUsageInspection */
/** @noinspection PhpUndefinedClassInspection */
/** @noinspection PhpUndefinedNamespaceInspection */

namespace EventManager\Modules\FrontendForm;

use AcfService\Contracts\EnqueueUploader;
use AcfService\Contracts\Form;
use AcfService\Contracts\FormHead;
use AcfService\Contracts\GetFieldGroups;
use AcfService\Implementations\NativeAcfService;
use EventManager\Modules\FrontendForm\FormStep;

use ComponentLibrary\Init as ComponentLibraryInit;
use WpService\Contracts\EnqueueStyle;
use WpService\Implementations\NativeWpService;
use Throwable;
use WpService\Contracts\__;

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

    private $formStepQueryParam = 'step'; // The query parameter for the form steps.
    private $formIdQueryParam   = 'formid'; // The query parameter for the form id.

    private $formPostType   = null; // The post type for the form.
    private $formPostStatus = null; // The post status for the form.

    private $blade = null;

    private EnqueueStyle&__ $wpService;
    private FormHead&EnqueueUploader&Form&GetFieldGroups $acfService;

    public function init(): void
    {
        $this->wpService  = new NativeWpService(); // TODO: use custom modularity middleware.
        $this->acfService = new NativeAcfService(); // TODO: use custom modularity middleware.

        $this->nameSingular = $this->wpService->__('Event Form');
        $this->namePlural   = $this->wpService->__('Event Forms');
        $this->description  = $this->wpService->__('Module for creating public event form');

        add_filter('query_vars', [$this, 'registerFormStepQueryVar']); // add from wpservice
        add_filter('query_vars', [$this, 'registerFormIdQueryVar']); // add from wpservice
        add_filter('acf/load_field/name=formStepGroup', [$this, 'addOptionsToGroupSelect']); // add from wpservice
    }

    private function preventFilteringWhenInEditMode() {
        global $post;
        if (is_a($post, 'WP_Post') && in_array(get_post_type($post), array('acf-field', 'acf-field-group'))) {
            return true;
        }
        return false;
    }

    public function data(): array
    {
        //Needs to be called, otherwise a notice will be thrown.
        $fields = (object) $this->getFields();

        //Empty form. Show message.
        if ($fields->formSteps == false) {
            return array_merge(
                $this->defaultDataResponse(),
                [
                'empty' => $this->renderView('partials.message', [
                    'text' => $this->wpService->__(
                        'No steps defined. Please Add some steps, to enable this form functionality.'
                    ),
                    'icon' => ['name' => 'info'],
                    'type' => 'info'
                ])
                ]
            );
        }

        //Protected form. Show message.
        if ($fields->isPublicForm == false && !$this->hasAccess()) {
            return array_merge(
                $this->defaultDataResponse(),
                [
                'empty' => $this->renderView('partials.message', [
                    'text' => $this->wpService->__('This form is protected, please log in.'),
                    'icon' => ['name' => 'info'],
                    'type' => 'info'
                ])
                ]
            );
        }

        //Define form steps
        $steps = [];
        foreach ($fields->formSteps as $index => $fieldGroup) {
            $steps[$index + 1] = new FormStep(
                $index + 1,
                $fieldGroup
            );
        }

        //Set form state
        $formState = new FormState(
            $steps,
            $this->formStepQueryParam
        );

        //Decorate step with state, and link
        foreach ($steps as &$step) {
            $step->state = $stepState = new FormStepState(
                $step,
                $formState,
                $steps
            );

            $step->nav = new FormStepNav(
                $step,
                $stepState,
                $steps,
                get_permalink(),
                [
                    'formid' => get_query_var($this->formIdQueryParam, "%post_id%"),
                    'step'   => null
                ]
            );
        }

        //Invalid step: Show error message
        if (!$formState->isValidStep) {
            return array_merge(
                $this->defaultDataResponse(),
                [
                'error' => $this->renderView('partials.message', [
                    'text' => $this->wpService->__('Whoops! It looks like we ran out of form.'),
                    'icon' => ['name' => 'error'],
                    'type' => 'warning'
                ])
                ]
            );
        }

        //Get current step form
        $self = $this; //Avoids lexical scope issues
        $form = (function ($step) use ($self) {
            $self->getForm($step, $self);
        });

        return array_merge(
            $this->defaultDataResponse(),
            [
                'error'        => false,
                'steps'        => $steps,
                'state'        => $formState,
                'form'         => $form,
                'formSettings' => (object) [
                    'postType'   => $fields->saveToPostType ?? "post",
                    'postStatus' => $fields->saveToPostTypeStatus ?? "draft"
                ],
                'lang'         => $this->getLang()
            ]
        );
    }

    /**
     * Retrives default values for keys used in the form display.
     *
     * @return array The default keys and values.
     */
    private function defaultDataResponse(): array
    {
        return [
            'empty' => false,
            'error' => false
        ];
    }

    /**
     * Check if the current user is logged in.
     */
    public function hasAccess(): bool
    {
        return is_user_logged_in();
    }

    /**
     * Retrives varous text strings for the form.
     *
     * @return object The (translated) text strings.
     */
    private function getLang(): object
    {
        $disclaimer = $this->wpService->__(
            <<<EOD
            By submitting this form, you're agreeing to our terms and conditions. 
            You're also consenting to us processing your personal data in line with GDPR regulations, 
            and confirming that you have full rights to use all provided content.
            EOD
        );

        return (object) [
            'disclaimer' => $disclaimer,
            'edit'       => $this->wpService->__('Edit'),
            'submit'     => $this->wpService->__('Submit'),
            'previous'   => $this->wpService->__('Previous'),
            'next'       => $this->wpService->__('Save and go to next step', 'api-event-manager'),
            'of'         => $this->wpService->__('of'),
            'step'       => $this->wpService->__('Step'),
            'completed'  => $this->wpService->__('Completed')
        ];
    }

    private function getQueryParam($key, $default = ""): string
    {
        return get_query_var($key, $default);
    }

    public function template(): string
    {
        return 'frontend-form.blade.php';
    }

    public function script(): void
    {
        $this->acfService->formHead();
        $this->acfService->enqueueUploader();
    }

    public function style(): void
    {
        $this->wpService->enqueueStyle('event-manager-frontend-form');
    }

    /**
     * Retrieves the form ID or "new_post".
     *
     * This method returns the form ID by retrieving the value of the specified query parameter.
     * If the query parameter is not set, the method will return a string representing create_new_post.
     *
     * @return string The form ID. Or "new_post" if the query parameter is not set.
     */
    private function getFormId(): string
    {
        return $this->getQueryParam($this->formIdQueryParam, 'new_post');
    }

    /**
     * Retrieves the form step.
     *
     * This method returns the form step by retrieving the value of the specified query parameter.
     *
     * @return int The form step.
     */
    public function getForm($step, $self): void
    {
        //Message when form is sent.
        $htmlUpdatedMessage = $self->renderView('partials.message', [
            'text' => '%s',
            'icon' => ['name' => 'info'],
            'type' => 'sucess'
        ]);

        $htmlSubmitButton = $self->renderView(
            'partials.button-wrapper',
            ['step' => $step]
        );

        $this->acfService->form([
            'post_id'               => $this->getFormId(),
            'return'                => $step->nav->next ?? false, // Add form result page here
            'post_title'            => $step->state->isFirst ? true : false,
            'post_content'          => false,
            'field_groups'          => [
                $step->group
            ],
            'form_attributes'       => ['class' => 'acf-form js-form-validation js-form-validation'],
            'uploader'              => 'basic',
            'updated_message'       => $this->wpService->__(
                "The event has been submitted for review. You will be notified when the event has been published."
            ),
            'html_updated_message'  => $htmlUpdatedMessage,
            'html_submit_button'    => $htmlSubmitButton,
            'new_post'              => [
                'post_type'   => $self->formPostType,
                'post_status' => $self->formPostStatus
            ],
            'instruction_placement' => 'field',
            'submit_value'          => $this->wpService->__('Create Event')
        ]);
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
            $this->blade->errorHandler($e)->print();
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
    public function registerFormStepQueryVar(array $registeredQueryVars): array
    {
        return $registeredQueryVars = array_merge(
            $registeredQueryVars,
            [$this->formStepQueryParam]
        );
    }

    /**
     * Registers the form id query variable.
     *
     * This method takes an array of registered query variables and adds the form id key to it.
     *
     * @param array $registeredQueryVars The array of registered query variables.
     * @return array The updated array of registered query variables.
     */
    public function registerFormIdQueryVar(array $registeredQueryVars): array
    {
        return $registeredQueryVars = array_merge(
            $registeredQueryVars,
            [$this->formIdQueryParam]
        );
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
        if($this->preventFilteringWhenInEditMode() === true) {
            return $field;
        }

        $field['choices'] = array();

        // Get all field groups, filter out all that are connected to a post type.
        $groups = $this->acfService->getFieldGroups();
        $groups = array_filter($groups, function ($item) {
            return isset($item['location'][0][0]['param']) && $item['location'][0][0]['param'] === 'post_type';
        });

        // Create a select item title
        $createSelectItemTitle = function ($name, $postTypeName) {
            $postTypeName = get_post_type_object($postTypeName);
            return (!empty($postTypeName->label) ? "$postTypeName->label: " : "") . $name;
        };

        // Add groups to the select field
        if (is_array($groups) && !empty($groups)) {
            foreach ($groups as $group) {
                $field['choices'][$group['key']] = $createSelectItemTitle(
                    $group['title'],
                    $group['location'][0][0]['value']
                );
            }
        }
        return $field;
    }
}
